<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIReportExtractorService;
use App\Models\CreditReport;
use App\Models\CreditScore;
use App\Models\CreditAccount;
use App\Models\CreditInquiry;
use App\Models\CreditPublicRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AIAnalysisController extends Controller
{
    /**
     * Run AI analysis on the user's latest credit report
     */
    public function analyze(Request $request)
    {
        $user = Auth::user();

        // Check if user has paid plan access (standard, pro, premium, or admin)
        $allowedPlans = ['starter', 'standard', 'pro', 'premium'];
        if (!in_array($user->plan_type, $allowedPlans) && $user->role !== 'admin') {
            return redirect()->route('plans')
                ->with('error', 'AI Analysis requires a paid plan. Please upgrade.');
        }

        // Get the report to analyze (specific ID or latest)
        if ($request->has('report_id')) {
            $latestReport = CreditReport::where('user_id', $user->id)
                ->where('id', $request->report_id)
                ->first();
                
            if (!$latestReport) {
                return redirect()->back()
                    ->with('error', 'Credit report not found.');
            }
        } else {
            // Get the latest credit report
            $latestReport = CreditReport::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        if (!$latestReport) {
            return redirect()->route('credit-vault')
                ->with('error', 'Please upload a credit report first before running AI analysis.');
        }

        // Check if force reanalyze is requested (for reimporting after disputes)
        $forceReanalyze = $request->get('force_reanalyze', false);

        // Check if this report has already been analyzed
        $hasExistingData = CreditScore::where('credit_report_id', $latestReport->id)->exists() ||
                          CreditAccount::where('credit_report_id', $latestReport->id)->exists() ||
                          CreditInquiry::where('credit_report_id', $latestReport->id)->exists();

        if ($hasExistingData && !$forceReanalyze) {
            // Already analyzed, just mark as completed and show results
            $user->update([
                'ai_analysis_completed' => true,
                'ai_analysis_completed_at' => now()
            ]);

            return redirect()->route('ai-analysis.results', ['id' => $latestReport->id])
                ->with('info', 'This report has already been analyzed. Showing existing results.');
        }

        // If force reanalyze, delete existing analysis data
        if ($forceReanalyze && $hasExistingData) {
            DB::beginTransaction();
            try {
                CreditScore::where('credit_report_id', $latestReport->id)->delete();
                CreditAccount::where('credit_report_id', $latestReport->id)->delete();
                CreditInquiry::where('credit_report_id', $latestReport->id)->delete();
                CreditPublicRecord::where('credit_report_id', $latestReport->id)->delete();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Failed to clear existing analysis data: ' . $e->getMessage());
            }
        }

        try {
            // Increase memory limit for large PDF processing
            ini_set('memory_limit', '2048M');
            DB::beginTransaction();

            // Read the stored file
            $filePath = storage_path('app/public/' . $latestReport->file_path);
            
            if (!file_exists($filePath)) {
                throw new \Exception('Credit report file not found at: ' . $filePath);
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $textContent = '';

            if ($extension === 'pdf') {
                // Parse PDF
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    $textContent = $pdf->getText();
                } catch (\Exception $e) {
                    throw new \Exception('Failed to extract text from PDF: ' . $e->getMessage());
                }
            } else {
                // Assume HTML
                $textContent = file_get_contents($filePath);
            }

            // Use AI to extract and categorize data (extractFromText handles both raw text and HTML)
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
                $latestReport->update([
                    'personal_info' => json_encode($parsedData['personal_info'])
                ]);
            }

            // Save credit scores
            if (!empty($parsedData['credit_scores']) && is_array($parsedData['credit_scores'])) {
                foreach ($parsedData['credit_scores'] as $scoreData) {
                    if (!is_array($scoreData)) continue;

                    CreditScore::create([
                        'user_id' => $user->id,
                        'credit_report_id' => $latestReport->id,
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
                    if (!is_array($accountData)) continue;

                    try {
                        CreditAccount::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $latestReport->id,
                            'creditor_name' => $sanitize($accountData['creditor_name'] ?? 'Unknown'),
                            'account_number' => $sanitize($accountData['account_number'] ?? null),
                            'account_type' => $sanitize($accountData['account_type'] ?? 'Unknown'),
                            'account_status' => $sanitize($accountData['status'] ?? $accountData['account_status'] ?? 'Unknown'),
                            'date_opened' => $sanitize($accountData['date_opened'] ?? null),
                            'date_reported' => $sanitize($accountData['date_reported'] ?? now()),
                            'credit_limit' => (float)($accountData['credit_limit'] ?? 0),
                            'current_balance' => (float)($accountData['balance'] ?? $accountData['current_balance'] ?? 0),
                            'amount_past_due' => (float)($accountData['amount_past_due'] ?? 0),
                            'payment_status' => $sanitize($accountData['status'] ?? 'Unknown'),
                            'bureau' => $sanitize($accountData['bureau'] ?? null),
                            'remarks' => $sanitize($accountData['dispute_reason'] ?? $accountData['remarks'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to save account: ' . $e->getMessage(), [
                            'account_data' => $accountData,
                            'exception' => $e
                        ]);
                        // Continue with next account instead of failing entirely
                        continue;
                    }
                }
            }

            // Save inquiries
            if (!empty($parsedData['inquiries']) && is_array($parsedData['inquiries'])) {
                foreach ($parsedData['inquiries'] as $inquiryData) {
                    if (!is_array($inquiryData)) continue;

                    try {
                        CreditInquiry::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $latestReport->id,
                            'creditor_name' => $sanitize($inquiryData['creditor_name'] ?? 'Unknown'),
                            'business_type' => $sanitize($inquiryData['business_type'] ?? null),
                            'inquiry_type' => $sanitize($inquiryData['inquiry_type'] ?? 'Hard'),
                            'inquiry_date' => $sanitize($inquiryData['inquiry_date'] ?? now()),
                            'bureau' => $sanitize($inquiryData['bureau'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to save inquiry: ' . $e->getMessage());
                        continue;
                    }
                }
            }

            // Save public records
            if (!empty($parsedData['public_records']) && is_array($parsedData['public_records'])) {
                foreach ($parsedData['public_records'] as $recordData) {
                    if (!is_array($recordData)) continue;

                    try {
                        CreditPublicRecord::create([
                            'user_id' => $user->id,
                            'credit_report_id' => $latestReport->id,
                            'record_type' => $sanitize($recordData['record_type'] ?? 'Unknown'),
                            'status' => $sanitize($recordData['status'] ?? 'Unknown'),
                            'amount' => (float)($recordData['amount'] ?? 0),
                            'date_filed' => $sanitize($recordData['date_filed'] ?? null),
                            'date_resolved' => $sanitize($recordData['date_resolved'] ?? null),
                            'court_info' => $sanitize($recordData['court_info'] ?? null),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to save public record: ' . $e->getMessage());
                        continue;
                    }
                }
            }

            // Mark AI analysis as completed
            $user->update([
                'ai_analysis_completed' => true,
                'ai_analysis_completed_at' => now()
            ]);

            DB::commit();

            $summary = $parsedData['summary'] ?? [];
            $totalAccounts = $summary['total_accounts'] ?? count($parsedData['accounts'] ?? []);
            $totalInquiries = $summary['total_inquiries'] ?? count($parsedData['inquiries'] ?? []);
            $totalScores = count($parsedData['credit_scores'] ?? []);

            return redirect()->route('ai-analysis.results', ['id' => $latestReport->id])
                ->with('success', "AI Analysis Complete! Categorized {$totalAccounts} accounts, {$totalInquiries} inquiries, and {$totalScores} credit scores.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('dashboard')
                ->with('error', 'AI Analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * Show AI analysis results
     */
    public function showResults($id = null)
    {
        $user = Auth::user();
        
        if (!$id) {
            // Find the latest report that has at least some credit score data (meaning it was analyzed)
            $latestReport = CreditReport::where('user_id', $user->id)
                ->whereHas('creditScores')
                ->latest()
                ->first();
                
            if (!$latestReport) {
                return redirect()->route('dashboard')
                    ->with('error', 'No analyzed credit report found. Please run an AI analysis first.');
            }
            $id = $latestReport->id;
        }

        $creditReport = CreditReport::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $creditScores = CreditScore::where('credit_report_id', $id)->get();
        $accounts = CreditAccount::where('credit_report_id', $id)->get();
        $inquiries = CreditInquiry::where('credit_report_id', $id)->get();
        $publicRecords = CreditPublicRecord::where('credit_report_id', $id)->get();

        // Categorize accounts - negative accounts have dispute reasons in remarks
        $negativeAccounts = $accounts->filter(function($account) {
            // Check if account has a dispute reason (remarks) - all negative accounts from AI have this
            return !empty($account->remarks) || 
                   in_array(strtolower($account->account_status), ['charge-off', 'collection', 'delinquent', 'late', 'charge off']);
        });

        $openAccounts = $accounts->filter(function($account) {
            return in_array(strtolower($account->account_status), ['open', 'current', 'active']);
        });

        $closedAccounts = $accounts->filter(function($account) {
            return in_array(strtolower($account->account_status), ['closed', 'paid']);
        });

        // Calculate average credit score
        $averageScore = $creditScores->count() > 0 ? round($creditScores->avg('score')) : null;

        return view('ai-analysis.results', compact(
            'creditReport',
            'creditScores',
            'accounts',
            'inquiries',
            'publicRecords',
            'negativeAccounts',
            'openAccounts',
            'closedAccounts',
            'averageScore'
        ));
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
