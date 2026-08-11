<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IdentityIQParserService;
use App\Services\AIReportExtractorService;
use App\Models\CreditReport;
use App\Models\CreditScore;
use App\Models\CreditAccount;
use App\Models\CreditInquiry;
use App\Models\CreditPublicRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class IdentityIQImportController extends Controller
{

    
    /**
     * Show the import form
     */
    public function index()
    {
        $user = Auth::user();
        $creditReports = CreditReport::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('identityiq.import', compact('creditReports'));
    }

    
    /**
     * Handle the file upload and import
     */
    public function import(Request $request)
    {
        $request->validate([
            'credit_report' => 'required|file|mimes:pdf,html,htm,txt|max:20480', // Max 20MB
        ]);

        $user = Auth::user();
        
        
        try {
            // Increase memory and execution time limits for large PDF/HTML processing with AI
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', '300'); // 5 minutes
            set_time_limit(300); // 5 minutes
            DB::beginTransaction();

            // Store the uploaded file explicitly to the public disk
            $file = $request->file('credit_report');
            $filename = 'credit_reports/' . $user->id . '/' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('', $filename, 'public');
            
            // Extract text from file based on extension
            $extension = strtolower($file->getClientOriginalExtension());
            $textContent = '';

            if ($extension === 'pdf') {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($file->getRealPath());
                    $textContent = $pdf->getText();
                } catch (\Exception $e) {
                    throw new \Exception('Failed to extract text from PDF. The file might be password protected or too complex. Error: ' . $e->getMessage());
                }
            } elseif (in_array($extension, ['html', 'htm', 'txt'])) {
                try {
                    $rawContent = file_get_contents($file->getRealPath());
                    if ($extension === 'txt') {
                        $textContent = $rawContent;
                    } else {
                        // Insert block-level spacing in HTML so parsed columns don't merge, then strip HTML tags
                        $spacedHtml = str_replace(
                            ['<tr', '<td', '<div', '<p', '</tr', '</td', '</div', '</p', '<br', '<li', '</li'],
                            ["\n<tr", " \t<td", "\n<div", "\n<p", "\n</tr", " \n</td", "\n</div", "\n</p", "\n<br", "\n<li", "\n</li"],
                            $rawContent
                        );
                        // Strip tags and decode entities
                        $textContent = html_entity_decode(strip_tags($spacedHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        // Clean up multiple spaces and double newlines
                        $textContent = preg_replace('/[ \t]+/', ' ', $textContent);
                        $textContent = preg_replace('/\n\s*\n/', "\n\n", $textContent);
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Failed to extract text from HTML/TXT file. Error: ' . $e->getMessage());
                }
            } else {
                throw new \Exception('Unsupported file format. Please upload a PDF, HTML, or TXT file.');
            }

            // Create credit report record
            $creditReport = CreditReport::create([
                'user_id' => $user->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filename,
                'extracted_text' => $textContent, // Store extracted text
            ]);

            // Use AI to extract data from text
            $aiExtractor = new AIReportExtractorService();
            $extractionResult = $aiExtractor->extractFromText($textContent);

            if (!$extractionResult['success']) {
                throw new \Exception('AI extraction failed: ' . $extractionResult['error']);
            }

            $parsedData = $extractionResult['data'];

            // Bulletproof sanitization function for all data types
            $sanitize = function($val) use (&$sanitize) {
                // Handle null
                if ($val === null || $val === '') return null;
                
                // Handle arrays - flatten to comma-separated string
                if (is_array($val)) {
                    $flattened = array_map($sanitize, $val);
                    $filtered = array_filter($flattened, fn($v) => $v !== null);
                    return empty($filtered) ? null : implode(', ', $filtered);
                }
                
                // Handle objects
                if (is_object($val)) {
                    if (method_exists($val, '__toString')) {
                        return (string)$val;
                    }
                    if ($val instanceof \DateTime || $val instanceof \Carbon\Carbon) {
                        return $val->format('Y-m-d H:i:s');
                    }
                    return null;
                }
                
                // Return scalars as-is
                return $val;
            };

            // Save personal info if available
            if (!empty($parsedData['personal_info'])) {
                $creditReport->update([
                    'personal_info' => json_encode($parsedData['personal_info'])
                ]);
            }

            // Save summary counts if available
            $summary = $parsedData['summary'] ?? [];
            if (!empty($summary)) {
                $creditReport->update([
                    'total_accounts_count' => $summary['total_accounts_transunion'] ?? $summary['total_accounts_experian'] ?? $summary['total_accounts_equifax'] ?? $summary['total_accounts'] ?? null,
                    'open_accounts_count' => $summary['open_accounts'] ?? null,
                    'negative_accounts_count' => $summary['derogatory_accounts'] ?? null,
                    'hard_inquiries_count' => $summary['hard_inquiries_2yr'] ?? null,
                ]);
            }

            // Save credit scores
            if (!empty($parsedData['credit_scores']) && is_array($parsedData['credit_scores'])) {
                foreach ($parsedData['credit_scores'] as $scoreData) {
                    if (!is_array($scoreData)) continue; // Skip malformed data
                    
                    CreditScore::create([
                        'user_id' => $user->id,
                        'credit_report_id' => $creditReport->id,
                        'bureau' => $scoreData['bureau'] ?? 'Unknown',
                        'score' => $scoreData['score'] ?? 0,
                        'score_model' => 'VantageScore',
                        'lender_rank' => $scoreData['lender_rank'] ?? $this->getLenderRank($scoreData['score'] ?? 0),
                        'score_scale' => '300-850',
                        'risk_factors' => [],
                        'report_date' => $scoreData['score_date'] ?? now(),
                    ]);
                }
            }

            // Save accounts
            if (!empty($parsedData['accounts']) && is_array($parsedData['accounts'])) {
                foreach ($parsedData['accounts'] as $accountData) {
                    if (!is_array($accountData)) continue; // Skip malformed data

                    try {
                        CreditAccount::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $creditReport->id,
                            'creditor_name' => $sanitize($accountData['creditor_name'] ?? 'Unknown'),
                            'account_number' => $sanitize($accountData['account_number'] ?? null),
                            'account_type' => $sanitize($accountData['account_type'] ?? 'Unknown'),
                            'account_status' => $sanitize($accountData['account_status'] ?? 'Unknown'),
                            'date_opened' => $sanitize($accountData['date_opened'] ?? null),
                            'date_reported' => $sanitize($accountData['date_reported'] ?? now()),
                            'credit_limit' => (float)($accountData['credit_limit'] ?? 0),
                            'current_balance' => (float)($accountData['current_balance'] ?? 0),
                            'amount_past_due' => (float)($accountData['amount_past_due'] ?? 0),
                            'payment_status' => $sanitize($accountData['payment_status'] ?? 'Unknown'),
                            'bureau' => $sanitize($accountData['bureau'] ?? null),
                            'remarks' => $sanitize($accountData['dispute_reason'] ?? $accountData['remarks'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to save account: " . $e->getMessage());
                        continue;
                    }
                }
            }

            // Save inquiries
            if (!empty($parsedData['inquiries']) && is_array($parsedData['inquiries'])) {
                foreach ($parsedData['inquiries'] as $inquiryData) {
                    if (!is_array($inquiryData)) continue; // Skip malformed data

                    try {
                        CreditInquiry::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $creditReport->id,
                            'creditor_name' => $sanitize($inquiryData['creditor_name'] ?? 'Unknown'),
                            'business_type' => $sanitize($inquiryData['business_type'] ?? null),
                            'inquiry_type' => $sanitize($inquiryData['inquiry_type'] ?? 'Hard'),
                            'inquiry_date' => $sanitize($inquiryData['inquiry_date'] ?? now()),
                            'bureau' => $sanitize($inquiryData['bureau'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to save inquiry: " . $e->getMessage());
                        continue;
                    }
                }
            }

            // Save public records
            if (!empty($parsedData['public_records']) && is_array($parsedData['public_records'])) {
                foreach ($parsedData['public_records'] as $recordData) {
                    if (!is_array($recordData)) continue; // Skip malformed data

                    try {
                        CreditPublicRecord::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $creditReport->id,
                            'record_type' => $sanitize($recordData['record_type'] ?? 'Unknown'),
                            'status' => $sanitize($recordData['status'] ?? 'Unknown'),
                            'amount' => (float)($recordData['amount'] ?? 0),
                            'date_filed' => $sanitize($recordData['date_filed'] ?? null),
                            'date_resolved' => $sanitize($recordData['date_resolved'] ?? null),
                            'court_info' => $sanitize($recordData['court_info'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Failed to save public record: " . $e->getMessage());
                        continue;
                    }
                }
            }

            DB::commit();

            // Use summary data for success message
            $totalAccounts = $summary['total_accounts_transunion'] ?? $summary['total_accounts_experian'] ?? $summary['total_accounts_equifax'] ?? $summary['total_accounts'] ?? count($parsedData['accounts'] ?? []);
            $openAccounts = $summary['open_accounts'] ?? 0;
            $negativeAccounts = $summary['derogatory_accounts'] ?? 0;
            $hardInquiries = $summary['hard_inquiries_2yr'] ?? count($parsedData['inquiries'] ?? []);

            return redirect()->route('identityiq.import')
                ->with('success', "Credit report imported successfully! Total Accounts: {$totalAccounts}, Open: {$openAccounts}, Negative: {$negativeAccounts}, Hard Inquiries: {$hardInquiries}");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to import credit report: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * View imported credit report data
     */
    public function show($id)
    {
        $user = Auth::user();
        $creditReport = CreditReport::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $creditScores = CreditScore::where('credit_report_id', $id)->get();
        $accounts = CreditAccount::where('credit_report_id', $id)->get();
        $inquiries = CreditInquiry::where('credit_report_id', $id)->get();
        $publicRecords = CreditPublicRecord::where('credit_report_id', $id)->get();

        // Use summary counts from the credit report (from AI extraction of Summary section)
        // These are more accurate than counting extracted accounts since AI can only extract a subset
        $totalAccountsCount = $creditReport->total_accounts_count ?? $accounts->count();
        $openAccountsCount = $creditReport->open_accounts_count ?? 0;
        $negativeAccountsCount = $creditReport->negative_accounts_count ?? 0;
        $hardInquiriesCount = $creditReport->hard_inquiries_count ?? $inquiries->count();

        // Categorize accounts - negative accounts have dispute reasons in remarks
        $negativeAccounts = $accounts->filter(function($account) {
            // Check if account has a dispute reason (remarks) - all negative accounts from AI have this
            return !empty($account->remarks) || 
                   in_array(strtolower($account->account_status), ['charge-off', 'collection', 'delinquent', 'late', 'charge off']);
        });

        $openAccounts = $accounts->filter(function($account) {
            // Account is open if status is 'Open' OR payment status is 'Current'
            return in_array(strtolower($account->account_status), ['open', 'current', 'active']) ||
                   in_array(strtolower($account->payment_status ?? ''), ['current']);
        });

        $closedAccounts = $accounts->filter(function($account) {
            return in_array(strtolower($account->account_status), ['closed', 'paid']);
        });

        // Calculate average credit score
        $averageScore = $creditScores->count() > 0 ? round($creditScores->avg('score')) : null;

        return view('identityiq.show', compact(
            'creditReport', 
            'creditScores', 
            'accounts', 
            'inquiries', 
            'publicRecords',
            'negativeAccounts',
            'openAccounts',
            'closedAccounts',
            'averageScore',
            'totalAccountsCount',
            'openAccountsCount',
            'negativeAccountsCount',
            'hardInquiriesCount'
        ));
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $creditReport = CreditReport::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // Delete the file
        if (Storage::exists('public/' . $creditReport->file_path)) {
            Storage::delete('public/' . $creditReport->file_path);
        }

        // Delete the record (cascade will handle related records)
        $creditReport->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Credit report deleted successfully.'
            ]);
        }

        return redirect()->route('identityiq.import')
            ->with('success', 'Credit report deleted successfully.');
    }

    /**
     * Get lender rank based on score
     */
    protected function getLenderRank($score)
    {
        if ($score >= 750) return 'Excellent';
        if ($score >= 700) return 'Good';
        if ($score >= 650) return 'Fair';
        if ($score >= 600) return 'Poor';
        return 'Very Poor';
    }
}
