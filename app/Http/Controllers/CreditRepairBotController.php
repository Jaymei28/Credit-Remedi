<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\DisputeLetter;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CreditReport;
use Spatie\PdfToText\Pdf as SpatiePdf;
use Smalot\PdfParser\Parser as SmalotParser;
use Illuminate\Support\Facades\Storage;
use App\Models\Creditor;
use App\Models\BureauAddress;
use App\Services\AuditService;
use App\Services\AIVisionExtractorService;
use App\Services\IdentityIQParserService;
use App\Services\PhasedLetterGenerator;
use App\Services\AIReportExtractorService;

class CreditRepairBotController extends Controller
{

    // my disputes
    public function index(Request $request)
    {
        $query = \App\Models\DisputeLetter::query();

        // If not admin, show only the user's own disputes
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // Apply status filter if present
        if ($request->has('status')) {
            if ($request->status === 'posted') {
                $query->where('posted_1', true);
            } elseif ($request->status === 'not_posted') {
                $query->where('posted_1', false);
            }
        }

        $disputes = $query->latest()->get();

        return view('disputes', compact('disputes'));
    }

    public function filter(Request $request)
    {
        $query = \App\Models\DisputeLetter::query();

        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        if ($request->status === 'posted') {
            $query->where('posted_1', true);
        } elseif ($request->status === 'not_posted') {
            $query->where('posted_1', false);
        }

        $disputes = $query->latest()->get();

        return view('partials.dispute-table', compact('disputes'))->render();
    }


    public function analyze(Request $request)
        {
            $reportId = $request->input('report_id');

            $letterContent = DB::table('dispute_letters')
                ->where('id', $reportId)
                ->value('letter_content');

            if (!$letterContent) {
                return response()->json(['error' => 'No letter_content found for this report'], 404);
            }

            $prompt = <<<EOT
            You are CreditRemedi, an AI that assists with credit remediation.
            Your task is to review the full CREDIT REPORT LETTER CONTENT and identify potential discrepancies.

            LETTER CONTENT TO ANALYZE:
            -----------------
            $letterContent
            -----------------

            Instructions:
            1. **Scan for Name Variations** – Flag if there are misspellings, shortened names, or multiple versions.  
            2. **Scan for Addresses** – Flag if addresses are inconsistent.  
            3. **Scan for SSN (last 4 only)** – Check if the last 4 digits of SSN are incomplete, incorrect, or mismatched.  
            4. **Scan for DOB** – Flag if DOB is inconsistent or missing.  
            5. **Generate Output:**  
            - Summary of flagged discrepancies.  
            - A remediation letter referencing these discrepancies.

            Format your response:

            ### Discrepancy Flags
            - Names: ...
            - Addresses: ...
            - SSN: ...
            - DOB: ...

            ### Remediation Letter
            [Letter here]
            EOT;

            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a credit remediation AI.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);

            $data = $response->json();

            $analysis = $data['choices'][0]['message']['content']
                ?? $data['choices'][0]['delta']['content']
                ?? '⚠️ No analysis returned';

            return response()->json([
                'report_id' => $reportId,
                'analysis' => $analysis, // always a string
            ]);
        }





    public function show($id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        // Check permission
        if (auth()->user()->role !== 'admin' && $dispute->user_id !== auth()->id()) {
            abort(403);
        }

        return view('disputes-show', compact('dispute'));
    }




    public function togglePost($id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        // Only allow owner or admin
        if (auth()->user()->role !== 'admin' && $dispute->user_id !== auth()->id()) {
            abort(403);
        }

        $dispute->posted_1 = !$dispute->posted_1;
        $dispute->posted_1_ts = $dispute->posted_1 ? now() : null;
        $dispute->save();

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', $dispute->posted_1 ? 'Marked as posted.' : 'Marked as unposted.');
    }

    public function updateSent(Request $request, $id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        $request->validate([
            'sent_date' => 'nullable|date',
        ]);

        $dispute->sent = $request->has('sent'); // checkbox returns null if unchecked
        $dispute->sent_date = $request->input('sent_date') ?? null;
        $dispute->sent_ts = now(); // always update timestamp when sent info is updated
        $dispute->save();

        return redirect()->back()->with('success', 'Sent info updated successfully.');
    }

    public function updateLetter(Request $request, $id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $dispute->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'letter_content' => 'required|string|min:20',
        ]);

        $dispute->letter_content = $validated['letter_content'];
        $dispute->save();

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Letter content updated successfully.');
    }


    public function downloadPdf($id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        // Check access: only admin or the owner can download
        if (auth()->user()->id !== $dispute->user_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (!$dispute->posted_1) {
            return redirect()->back()->with('error', 'You can only download a finalized (posted) letter.');
        }

        $pdf = Pdf::loadView('pdf.dispute-letter', compact('dispute'));

        $filename = 'DisputeLetter_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function downloadFollowUpPdf($id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        if (!$dispute->letter_content_2) {
            return back()->with('error', 'No follow-up letter found to download.');
        }

        $pdf = Pdf::loadView('pdf.followup', ['dispute' => $dispute]);

        $filename = 'FollowUp_Letter_Dispute_' . $dispute->id . '.pdf';

        return $pdf->download($filename);
    }

    public function generateFollowUp(Request $request, $id)
    {
        $dispute = DisputeLetter::findOrFail($id);

        // ✅ If follow-up letter is already saved, just show it
        if (!empty($dispute->letter_content_2)) {
            return view('disputes-followup', [
                'dispute' => $dispute,
                'followUpLetter' => $dispute->letter_content_2,
            ]);
        }


        $date_today = now()->format('F j, Y');


        // Define the system and user messages for Chat API
        $apiMessages = [
            [
                'role' => 'system',
                'content' => 'You are Credit Remedi AI, a professional credit repair assistant. You generate a stronger, escalated follow-up variation of the original dispute letter, suitable for sending 15 days later if no response was received. 📅 Today is '.$date_today.'. Ensure the letter header includes ONLY {Name}, {Address Line}, SSN: XXX-XX-{{Last 4}}, and Date. Do not include any commentary or instructions—only output the follow-up letter itself.',
            ],
            [
                'role' => 'user',
                'content' => $dispute->letter_content,
            ]
        ];

        // Send request to OpenAI Chat API
        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => $apiMessages,
                'temperature' => 0.7,
            ]);

        // Handle possible API failure
        if (!$response->successful()) {
            return back()->with('error', 'OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        $followUpLetter = $data['choices'][0]['message']['content'] ?? null;

        if ($followUpLetter) {
            $dispute->letter_content_2 = $followUpLetter;
            $dispute->letter_content_2_ts = Carbon::now();
            $dispute->save();
        }
    
        return view('disputes-followup', [
            'dispute' => $dispute,
            'followUpLetter' => $followUpLetter ?? 'No response received.',
        ]);
    }


    // end my disputes
    

    /**
     * Search creditors for autocomplete
     */
    public function searchCreditors(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $creditors = Creditor::where('name', 'LIKE', "%{$query}%")
            ->orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'type']);
        
        return response()->json($creditors);
    }

    public function showChat()
    {
        $user = auth()->user();

        // Redirect if user is not admin and not on supported plans
        if ($user->role !== 'admin' && !in_array($user->plan_type, ['starter', 'standard', 'pro', 'premium'])) {
            return redirect('/dashboard')->with('error', 'Access denied. Please subscribe to a supported plan to access the credit repair assistant.');
        }

        // Get start and end of current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Count letters submitted this month by the user
        $letterCount = DisputeLetter::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        if ($letterCount >= 50) {
            return redirect('/dashboard')->with('error', 'You\'ve reached your monthly limit of dispute letters (5 letters). Upgrade to Turbo for unlimited dispute letter + full access to AI tools and community support.');
        }
        
        // Load session messages or default to empty array
        $messages = session('messages', []);

        // If no messages exist yet, set the default assistant greeting
        if (empty($messages)) {
            $messages[] = [
                'role' => 'assistant',
                'content' => "Hey 👋 I’m Ally, your AI Credit Ally.\n\nI’m here to help you understand your credit, create dispute letters, and stay organized through the process.",
                'type' => 'options',
                'options' => [
                    ['label' => 'Click here to start disputing', 'value' => 'Create Dispute Letter'],
                    ['label' => 'View My Disputes', 'value' => 'View Disputes'],
                    ['label' => 'How Credit Repair Works', 'value' => 'How it works'],
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            // Save to session so the Blade view can access it
            session(['messages' => $messages]);
        }

        return view('credit-repair-bot', compact('messages'));
    }


    /**
     * Check if message should use guided conversation (quick replies)
     * Returns structured response or null if should use AI
     */
    private function getGuidedResponse(string $message, array $state): ?array
    {
        $messageLower = strtolower(trim($message));
        $currentStep = $state['step'] ?? 'initial';

        // DETECT PRE-FILLED DISPUTE FROM IDENTITYIQ
        if (strpos($message, 'Generate a dispute letter for this account:') !== false) {
            // Extract information from the auto-generated message
            preg_match('/Creditor:\s*(.+)/m', $message, $creditorMatch);
            preg_match('/Account Type:\s*(.+)/m', $message, $accountTypeMatch);
            preg_match('/Status:\s*(.+)/m', $message, $statusMatch);
            preg_match('/Bureau:\s*(.+)/m', $message, $bureauMatch);
            preg_match('/Dispute Reason:\s*(.+)/m', $message, $reasonMatch);
            
            $creditor = isset($creditorMatch[1]) ? trim($creditorMatch[1]) : '';
            $accountType = isset($accountTypeMatch[1]) ? trim($accountTypeMatch[1]) : '';
            $status = isset($statusMatch[1]) ? trim($statusMatch[1]) : '';
            $bureau = isset($bureauMatch[1]) ? trim($bureauMatch[1]) : '';
            $reason = isset($reasonMatch[1]) ? trim($reasonMatch[1]) : '';
            
            // Pre-fill the state with extracted information
            $state['data']['creditor'] = $creditor;
            $state['data']['account_type'] = $accountType;
            $state['data']['account_status'] = $status;
            $state['data']['bureau'] = $bureau;
            $state['data']['dispute_reason'] = $reason;
            
            // Determine if it's all bureaus or specific
            if (strpos(strtolower($bureau), 'all') !== false) {
                $state['data']['generate_3_letters'] = true;
            }
            
            // Return a confirmation message and ask for account number
            return [
                'text' => "I've received your dispute request from your credit report analysis! 📊\n\n**Here's what I captured:**\n\n📋 **Bureau:** {$bureau}\n📁 **Account Type:** {$accountType}\n⚠️ **Dispute Reason:** {$reason}\n💳 **Creditor:** {$creditor}\n\nWhat is the account number for this {$creditor} account? (Type 'N/A' if you don't know or it's not available)",
                'type' => 'text',
                'options' => [
                    ['label' => "I don't know", 'value' => 'N/A'],
                ],
                'state' => ['step' => 'enter_account', 'data' => $state['data']]
            ];
        }

        // INITIAL GREETING - Show main menu
        if (in_array($messageLower, ['hi', 'hello', 'hey', 'start']) || $currentStep === 'initial') {
            return [
                'text' => "Hey 👋 I’m Ally, your AI Credit Ally.\n\nI'm here to guide you step-by-step through fixing your credit — from creating dispute letters to removing negative items and even helping you file complaints when needed.\n\nWhat would you like to do today?",
                'type' => 'options',
                'options' => [
                    ['label' => '⚠️ Create Dispute Letter', 'value' => 'Create Dispute Letter'],
                    ['label' => '📊 View My Disputes', 'value' => 'View Disputes'],
                    ['label' => '❓ How Credit Repair Works', 'value' => 'How it works'],
                ],
                'state' => ['step' => 'main_menu', 'data' => []]
            ];
        }

        // User wants to create dispute letter
        if ($messageLower === 'create dispute letter' || 
            $messageLower === 'dispute letter' || 
            $messageLower === 'click here to start disputing') {
            return [
                'text' => "Is this item reporting on all 3 credit bureaus (Equifax, Experian, and TransUnion)?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Yes - All 3 Bureaus', 'value' => 'Yes'],
                    ['label' => 'No - Specific Bureau', 'value' => 'No'],
                ],
                'state' => ['step' => 'ask_all_bureaus', 'data' => []]
            ];
        }

        // If Yes to all bureaus, skip to account type
        if ($currentStep === 'ask_all_bureaus' && ($messageLower === 'yes' || $messageLower === 'yes - all 3 bureaus')) {
            $state['data']['bureau'] = 'All 3 Bureaus';
            $state['data']['generate_3_letters'] = true;
            
            return [
                'text' => "Great! I'll generate 3 separate letters (one for each bureau).\n\nWhat type of account or item are you disputing?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Collection', 'value' => '1'],
                    ['label' => 'Charge-off', 'value' => '2'],
                    ['label' => 'Late Payment', 'value' => '3'],
                    ['label' => 'Bankruptcy', 'value' => '4'],
                    ['label' => 'Personal Information', 'value' => '5'],
                ],
                'state' => ['step' => 'ask_account_type', 'data' => $state['data']]
            ];
        }

        // If No to all bureaus, ask which specific bureau
        if ($currentStep === 'ask_all_bureaus' && ($messageLower === 'no' || $messageLower === 'no - specific bureau')) {
            return [
                'text' => "Which credit bureau is reporting this item?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Equifax', 'value' => 'Equifax'],
                    ['label' => 'Experian', 'value' => 'Experian'],
                    ['label' => 'TransUnion', 'value' => 'TransUnion'],
                    ['label' => 'Secondary Bureaus', 'value' => 'Secondary Bureaus'],
                ],
                'state' => ['step' => 'ask_bureau', 'data' => []]
            ];
        }

        // Bureau selected - Ask account type
        if ($currentStep === 'ask_bureau' && (in_array($message, ['1', '2', '3', '4']) || in_array($messageLower, ['equifax', 'experian', 'transunion', 'secondary bureaus']))) {
            
            // Handle Secondary Bureaus selection
            if ($message === '4' || $messageLower === 'secondary bureaus') {
                return [
                    'text' => "Which secondary bureau is reporting this item?",
                    'type' => 'options',
                    'options' => [
                        ['label' => 'Innovis', 'value' => 'Innovis'],
                        ['label' => 'LexisNexis', 'value' => 'LexisNexis'],
                        ['label' => 'ChexSystems', 'value' => 'ChexSystems'],
                        ['label' => 'ARS', 'value' => 'ARS'],
                        ['label' => 'Sagestream', 'value' => 'Sagestream'],
                        ['label' => 'CoreLogic', 'value' => 'CoreLogic'],
                        ['label' => 'Clarity Services', 'value' => 'Clarity Services'],
                    ],
                    'state' => ['step' => 'ask_secondary_bureau', 'data' => $state['data']]
                ];
            }

            // Handle Primary Bureaus
            $bureauMap = [
                '1' => 'Equifax',
                '2' => 'Experian', 
                '3' => 'TransUnion',
                'equifax' => 'Equifax',
                'experian' => 'Experian',
                'transunion' => 'TransUnion',
            ];
            
            $bureauKey = in_array($message, ['1', '2', '3']) ? $message : $messageLower;
            $state['data']['bureau'] = $bureauMap[$bureauKey];
            $state['data']['bureau_number'] = in_array($message, ['1', '2', '3']) ? $message : array_search($bureauMap[$bureauKey], ['1' => 'Equifax', '2' => 'Experian', '3' => 'TransUnion']);
            
            return [
                'text' => "What type of account or item are you disputing?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Collection', 'value' => '1'],
                    ['label' => 'Charge-off', 'value' => '2'],
                    ['label' => 'Late Payment', 'value' => '3'],
                    ['label' => 'Bankruptcy', 'value' => '4'],
                    ['label' => 'Personal Information', 'value' => '5'],
                ],
                'state' => ['step' => 'ask_account_type', 'data' => $state['data']]
            ];
        }

        // Secondary Bureau Selected
        if ($currentStep === 'ask_secondary_bureau') {
            $state['data']['bureau'] = $message;
            
            return [
                'text' => "What type of account or item are you disputing?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Collection', 'value' => '1'],
                    ['label' => 'Charge-off', 'value' => '2'],
                    ['label' => 'Late Payment', 'value' => '3'],
                    ['label' => 'Bankruptcy', 'value' => '4'],
                    ['label' => 'Personal Information', 'value' => '5'],
                ],
                'state' => ['step' => 'ask_account_type', 'data' => $state['data']]
            ];
        }

        // Account type selected - Check if Personal Info (special flow)
        if ($currentStep === 'ask_account_type' && ($message === '5' || $messageLower === 'personal information')) {
            $state['data']['account_type'] = 'Personal Information';
            $state['data']['account_type_number'] = '5';
            
            return [
                'text' => "You selected: **Personal Information**\n\nWhat type of errors does your Personal Information have?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Name Errors', 'value' => '1'],
                    ['label' => 'Address Errors', 'value' => '2'],
                    ['label' => 'SSN or DOB', 'value' => '3'],
                    ['label' => 'Employment History', 'value' => '4'],
                    ['label' => 'Phone Numbers', 'value' => '5'],
                    ['label' => 'Spouse/Co-Applicant', 'value' => '6'],
                ],
                'state' => ['step' => 'personal_info_type', 'data' => $state['data']]
            ];
        }

        // Personal info type selected - Ask for details (then switch to AI)
        if ($currentStep === 'personal_info_type' && in_array($message, ['1', '2', '3', '4', '5', '6'])) {
            $types = [
                '1' => 'Name Errors',
                '2' => 'Address Errors', 
                '3' => 'SSN or DOB',
                '4' => 'Employment History',
                '5' => 'Phone Numbers',
                '6' => 'Spouse/Co-Applicant'
            ];
            $state['data']['personal_info_type'] = $types[$message];
            
            // Return to AI for the rest of the flow
            return [
                'text' => "You've indicated errors in your **{$types[$message]}**.\n\nPlease list the specific incorrect {$types[$message]} exactly as they appear on your report, and provide the correct information.\n\n**Example:**\nIncorrect: [Item as shown on report]\nCorrect: [The correct information]",
                'type' => 'text',
                'options' => [],
                'state' => ['step' => 'ai_takeover', 'data' => $state['data']],
                'use_ai' => true // Signal to use AI from here
            ];
        }

        // Account type selected (non-personal info) - Show dispute reason options
        if ($currentStep === 'ask_account_type' && (in_array($message, ['1', '2', '3', '4']) || in_array($messageLower, ['collection', 'charge-off', 'late payment', 'bankruptcy']))) {
            $typeMap = [
                '1' => 'Collection',
                '2' => 'Charge-off',
                '3' => 'Late Payment',
                '4' => 'Bankruptcy',
                'collection' => 'Collection',
                'charge-off' => 'Charge-off',
                'late payment' => 'Late Payment',
                'bankruptcy' => 'Bankruptcy',
            ];
            
            $typeKey = in_array($message, ['1', '2', '3', '4']) ? $message : $messageLower;
            $state['data']['account_type'] = $typeMap[$typeKey];
            $state['data']['account_type_number'] = in_array($message, ['1', '2', '3', '4']) ? $message : array_search($typeMap[$typeKey], ['1' => 'Collection', '2' => 'Charge-off', '3' => 'Late Payment', '4' => 'Bankruptcy']);
            
            // Define account-type-specific dispute reasons
            $disputeReasons = [];
                
            switch ($state['data']['account_type']) {
                case 'Collection':
                    $disputeReasons = [
                        ['label' => 'Not/Mine Identity Theft', 'value' => 'Not/Mine Identity Theft'],
                    ];
                    break;
                    
                case 'Charge-off':
                    $disputeReasons = [
                        ['label' => 'Monthly Reporting', 'value' => 'Monthly Reporting'],
                        ['label' => 'Transferred Debt', 'value' => 'Transferred Debt'],
                        ['label' => 'Non-Reporting', 'value' => 'Non-Reporting'],
                    ];
                    break;
                    
                case 'Late Payment':
                    $disputeReasons = [
                        ['label' => 'Payment Was On Time', 'value' => 'Payment Was On Time'],
                        ['label' => 'Incorrect Date', 'value' => 'Incorrect Date'],
                        ['label' => 'Payment Not Received by Creditor', 'value' => 'Payment Not Received by Creditor'],
                        ['label' => 'Older Than 7 Years', 'value' => 'Older Than 7 Years'],
                        ['label' => 'Creditor Error', 'value' => 'Creditor Error'],
                        ['label' => 'Duplicate Late Payment', 'value' => 'Duplicate Late Payment'],
                    ];
                    break;
                    
                case 'Bankruptcy':
                    $disputeReasons = [
                        ['label' => 'Inaccurate Reporting', 'value' => 'Inaccurate Reporting'],
                    ];
                    break;
                    

                    
                default:
                    $disputeReasons = [
                        ['label' => 'Not My Account', 'value' => 'Not My Account'],
                        ['label' => 'Incorrect Information', 'value' => 'Incorrect Information'],
                        ['label' => 'Account Too Old', 'value' => 'Account Too Old'],
                    ];
            }
            
            $bureauInfo = isset($state['data']['generate_3_letters']) && $state['data']['generate_3_letters'] 
                ? "📋 **Bureau:** All 3 Bureaus (Equifax, Experian, TransUnion)\n" 
                : "📋 **Bureau:** {$state['data']['bureau']}\n";
            
            return [
                'text' => "Great! Here's what you've selected:\n\n{$bureauInfo}📁 **Account Type:** {$typeMap[$typeKey]}\n\n**What is your reason for disputing this item?**\n\nSelect the option that best describes why you're disputing:",
                'type' => 'options',
                'options' => $disputeReasons,
                'state' => ['step' => 'ask_dispute_reason', 'data' => $state['data']]
            ];
        }
        
        // Dispute reason selected - Move to Continue
        if ($currentStep === 'ask_dispute_reason') {
            $state['data']['dispute_reason'] = $message;
            
            $bureauInfo = isset($state['data']['generate_3_letters']) && $state['data']['generate_3_letters'] 
                ? "📋 **Bureau:** All 3 Bureaus (Equifax, Experian, TransUnion)\n" 
                : "📋 **Bureau:** {$state['data']['bureau']}\n";
            
            return [
                'text' => "Perfect! Here's your dispute summary:\n\n{$bureauInfo}📁 **Account Type:** {$state['data']['account_type']}\n⚠️ **Dispute Reason:** {$message}\n\nClick **Continue** to proceed with creating your dispute letter.",
                'type' => 'options',
                'options' => [
                    ['label' => 'Continue', 'value' => 'continue_dispute'],
                    ['label' => '← Go Back', 'value' => 'Create Dispute Letter'],
                ],
                'state' => ['step' => 'confirm_account_type', 'data' => $state['data']]
            ];
        }

        // User clicked Continue after confirming account type - Switch to AI
        if ($currentStep === 'confirm_account_type' && ($messageLower === 'continue' || $messageLower === 'continue_dispute')) {
            // Don't return a message here - let the AI ask the first question directly
            // Just mark that we're ready for AI takeover
            $stateData = $state['data'] ?? [];
            session(['bot_state' => ['step' => 'ai_takeover', 'data' => $stateData]]);
            // Continue to AI processing below (don't return early)
        }

        // View disputes
        if ($messageLower === 'view disputes') {
            return [
                'text' => "Redirecting you to **My Disputes** page...",
                'type' => 'redirect',
                'redirect_url' => '/my-disputes',
                'state' => ['step' => 'main_menu', 'data' => []]
            ];
        }

        // How it works
        if ($messageLower === 'how it works') {
            return [
                'text' => "**How Credit Repair Works:**\n\n1. **Identify Errors** - Review your credit report for inaccuracies\n2. **Dispute Items** - Send legal dispute letters to credit bureaus\n3. **Follow Up** - Track responses and send follow-up letters\n4. **Escalate** - File complaints with CFPB if needed\n\nI can help you with all of these steps!",
                'type' => 'options',
                'options' => [
                    ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                    ['label' => '⚠️ Create Dispute Letter', 'value' => 'Create Dispute Letter'],
                ],
                'state' => ['step' => 'main_menu', 'data' => []]
            ];
        }
        
        
        // View specific letter
        if (Str::startsWith($messageLower, 'view_letter_')) {
            $letterId = str_replace('view_letter_', '', $messageLower);
            return [
                'text' => "Opening your dispute letter...\n\n[Click here to view letter details](/disputes/{$letterId})",
                'type' => 'options',
                'options' => [
                    ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                    ['label' => '➕ Create Another Letter', 'value' => 'Create Dispute Letter'],
                ],
                'state' => ['step' => 'main_menu', 'data' => []]
            ];
        }

        // --- MISSING LOGIC RESTORED ---
        
        // CREDITOR ENTERED - ASK FOR ACCOUNT NUMBER
        if ($currentStep === 'enter_creditor') {
            $state['data']['creditor'] = $message;
            return [
                'text' => "Creditor: **{$message}**\n\nWhat is the account number? (Type 'N/A' if you don't know)",
                'type' => 'text',
                'options' => [
                    ['label' => "I don't know", 'value' => 'N/A'],
                ],
                'state' => ['step' => 'enter_account', 'data' => $state['data']]
            ];
        }

        // ACCOUNT ENTERED - GENERATE LETTER (Logic for Auto-Dispute / Generate Now)
        if ($currentStep === 'enter_account') {
            $state['data']['account_number'] = $message;
            
            // Build a preview of the letter content (simplified)
            $accountType = $state['data']['account_type'] ?? 'Account';
            $creditor = $state['data']['creditor'] ?? 'N/A';
            
            $preview = "**Dispute Details:**\n" .
                       "• Account Type: {$accountType}\n" .
                       "• Creditor: {$creditor}\n" .
                       "• Account #: {$message}";

            // Save state to session
            session(['bot_state' => $state]);

            return [
                'text' => "✅ **Details Received!**\n\n{$preview}\n\nClick 'Generate Now' to create your dispute letter using the advanced legal strategy.",
                'type' => 'options',
                'options' => [
                    ['label' => '⚡ Generate Now', 'value' => 'Generate Now'],
                    ['label' => '➕ Add Another Account', 'value' => 'Add Another'],
                ],
                'state' => ['step' => 'letter_generated', 'data' => $state['data']]
            ];
        }

        // No guided response - use AI
        return null;
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = trim($validated['message']);
        
        // Get conversation state from session
        $conversationState = session('bot_state', [
            'step' => 'initial',
            'data' => []
        ]);

        // Check if we should use guided conversation (quick replies)
        $guidedResponse = $this->getGuidedResponse($userMessage, $conversationState);
        
        if ($guidedResponse && !($guidedResponse['use_ai'] ?? false)) {
            // Use guided response with quick reply buttons
            $messages = session('messages', []);
            
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
                'timestamp' => now()->toIso8601String(),
            ];

            $messages[] = [
                'role' => 'assistant',
                'content' => $guidedResponse['text'],
                'type' => $guidedResponse['type'],
                'options' => $guidedResponse['options'] ?? [],
                'timestamp' => now()->toIso8601String(),
            ];

            session(['messages' => $messages]);
            session(['bot_state' => $guidedResponse['state']]);

            return response()->json([
                'success' => true,
                'message' => $guidedResponse['text'],
                'type' => $guidedResponse['type'],
                'options' => $guidedResponse['options'] ?? [],
                'messages' => $messages,
            ]);
        }

        // If guided response signals AI takeover, inject context into AI prompt
        $guidedContext = '';
        if ($guidedResponse && ($guidedResponse['use_ai'] ?? false)) {
            // SAVE STATE! This was the missing piece.
            session(['bot_state' => $guidedResponse['state']]);
            
            // Build context from collected data
            $data = $guidedResponse['state']['data'] ?? [];
            if (!empty($data)) {
                $guidedContext = "\n\n🤖 CONTEXT FROM GUIDED FLOW:\n";
                if (isset($data['bureau'])) {
                    $bureauNum = $data['bureau_number'] ?? 'N/A';
                    $guidedContext .= "- Bureau: {$data['bureau']} (User selected option {$bureauNum})\n";
                }
                if (isset($data['account_type'])) {
                    $accountTypeNum = $data['account_type_number'] ?? 'N/A';
                    $guidedContext .= "- Account Type: {$data['account_type']} (User selected option {$accountTypeNum})\n";
                }
                if (isset($data['dispute_reason'])) {
                    $guidedContext .= "- Dispute Reason: {$data['dispute_reason']}\n";
                }
                if (isset($data['personal_info_type'])) {
                    $guidedContext .= "- Personal Info Type: {$data['personal_info_type']}\n";
                }
                $guidedContext .= "\n**IMPORTANT**: The user has already selected the bureau, account type, and dispute reason in the guided flow. DO NOT ask for the dispute reason again. Proceed directly to asking for:\n1. Creditor/Collection Agency name\n2. Account number\n3. Any additional details needed for the dispute letter\n\nSkip any questions about dispute reasons - we already have that information above.\n";
            }
            
            // Save the guided response message first
            $messages = session('messages', []);
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
                'timestamp' => now()->toIso8601String(),
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => $guidedResponse['text'],
                'type' => $guidedResponse['type'],
                'options' => $guidedResponse['options'] ?? [],
                'timestamp' => now()->toIso8601String(),
            ];
            session(['messages' => $messages]);
            
            // Update local conversation state variable so subsequent logic uses the new state
            $conversationState = $guidedResponse['state'];
        }
        
        // Detect auto-dispute from AI Analysis (has all account details)
        // OR Manual Flow "Generate Now" button click
        $isGenerateNow = ($userMessage === 'Generate Now');
        
        $isAutoDispute = $isGenerateNow || (
                        str_contains($userMessage, 'Generate a dispute letter for this account') &&
                        str_contains($userMessage, 'Creditor:') &&
                        str_contains($userMessage, 'Account Type:') &&
                        str_contains($userMessage, 'Bureau:')
                        );
        
        $autoDisputeContext = '';
        if ($isAutoDispute) {
            $autoDisputeContext = "\n\n🎯 AUTO-DISPUTE MODE:\n";
            $autoDisputeContext .= "The user has provided ALL required information in their message.\n";
            $autoDisputeContext .= "DO NOT ask any questions. Generate the dispute letter IMMEDIATELY using the information provided.\n";

            // Extract Account Type to select template and apply PDF strategy
            $accountType = 'general';
            
            // CASE 1: Manual Flow (Data in Session)
            if ($isGenerateNow) {
                // Get data from session state if available, or fall back to previous messages
                $data = $conversationState['data'] ?? [];
                $typeStr = strtolower($data['account_type'] ?? '');
                
                if (empty($typeStr)) {
                    // Try to find it in the message history if session is empty (improbable but safe)
                     if (preg_match('/Account Type:\s*(.*?)(?:\n|$)/i', $guidedContext, $m)) {
                         $typeStr = strtolower($m[1]);
                     }
                }

                if (str_contains($typeStr, 'collection')) $accountType = 'collection';
                elseif (str_contains($typeStr, 'charge') || str_contains($typeStr, 'off')) $accountType = 'chargeoff';
                elseif (str_contains($typeStr, 'late')) $accountType = 'late_payment';
                elseif (str_contains($typeStr, 'inquiry')) $accountType = 'inquiry';
                elseif (str_contains($typeStr, 'bankrupt')) $accountType = 'bankruptcy';
                
                // INJECT SESSION DATA FOR AI CONTEXT
                // Crucial: Explicitly tell AI the details so it doesn't hallucinate or use "Generate Now" as data
                $autoDisputeContext .= "\nDETAILS FROM SESSION:\n";
                $autoDisputeContext .= "Creditor: " . ($data['creditor'] ?? 'N/A') . "\n";
                $autoDisputeContext .= "Account Number: " . ($data['account_number'] ?? 'N/A') . "\n";
                $autoDisputeContext .= "Bureau: " . ($data['bureau'] ?? 'N/A') . "\n";
                $autoDisputeContext .= "Dispute Reason: " . ($data['dispute_reason'] ?? 'N/A') . "\n";
            }
            // CASE 2: Auto-Dispute (Data in Message Text)
            elseif (preg_match('/Account Type:\s*(.*?)(?:\n|$)/i', $userMessage, $matches)) {
                $typeStr = strtolower(trim($matches[1]));
                if (str_contains($typeStr, 'collection')) $accountType = 'collection';
                elseif (str_contains($typeStr, 'charge') || str_contains($typeStr, 'off')) $accountType = 'chargeoff';
                elseif (str_contains($typeStr, 'late')) $accountType = 'late_payment';
                elseif (str_contains($typeStr, 'inquiry')) $accountType = 'inquiry';
                elseif (str_contains($typeStr, 'bankrupt')) $accountType = 'bankruptcy';

                // Extract other fields to inject into context
                $autoDisputeContext .= "\nDETAILS FROM USER MESSAGE:\n";
                
                if (preg_match('/Creditor:\s*(.*?)(?:\n|$)/i', $userMessage, $m)) {
                    $autoDisputeContext .= "Creditor: " . trim($m[1]) . "\n";
                }
                if (preg_match('/Bureau:\s*(.*?)(?:\n|$)/i', $userMessage, $m)) {
                     $autoDisputeContext .= "Bureau: " . trim($m[1]) . "\n";
                }
                if (preg_match('/Account (?:#|Number):\s*(.*?)(?:\n|$)/i', $userMessage, $m)) {
                     $autoDisputeContext .= "Account Number: " . trim($m[1]) . "\n";
                }
                if (preg_match('/Status:\s*(.*?)(?:\n|$)/i', $userMessage, $m)) {
                     $autoDisputeContext .= "Status: " . trim($m[1]) . "\n";
                }
                if (preg_match('/Dispute Reason:\s*(.*?)(?:\n|$)/i', $userMessage, $m)) {
                     $autoDisputeContext .= "Dispute Reason: " . trim($m[1]) . "\n";
                }
            }

            // Fetch template strategy from DB
            $template = \App\Models\BotPrompt::where('key', 'template_' . $accountType)->value('content');
            
            if ($template) {
                $autoDisputeContext .= "Use the following specific strategy and format for the letter body:\n";
                $autoDisputeContext .= "'''\n$template\n'''\n";
                $autoDisputeContext .= "Ensure you follow the legal citations (e.g. 15 U.S.C.) mentioned in the template exactly.\n";
            } else {
                 $autoDisputeContext .= "Use a standard FCRA dispute template.\n";
            }

            // CRITICAL: Forbid headers to prevent conflicts/duplicates
            $autoDisputeContext .= "CRITICAL INSTRUCTION: DO NOT include the bureau name, address, or date at the top of the letter.\n";
            $autoDisputeContext .= "The system already adds the correct header. Start your response directly with 'Subject:' or the salutation.\n";

            $autoDisputeContext .= "Use the == BEGIN LETTER == and == END LETTER == markers.\n\n";
        }

        // Continue with AI-generated response
        $date_today = now()->format('F j, Y');


        $authUser = auth()->user();

        // Fetch user's imported IdentityIQ reports
        $creditReports = CreditReport::where('user_id', $authUser->id)
            ->with(['creditScores', 'creditAccounts', 'creditInquiries'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Build credit report summary for AI context
        $creditReportContext = "";
        if ($creditReports->isNotEmpty()) {
            $latestReport = $creditReports->first();
            $creditReportContext = "\n\n📊 USER'S IMPORTED CREDIT REPORT DATA:\n";
            $creditReportContext .= "Report: {$latestReport->original_filename}\n";
            $creditReportContext .= "Imported: {$latestReport->created_at->format('M d, Y')}\n\n";
            
            // Credit Scores
            if ($latestReport->creditScores->isNotEmpty()) {
                $creditReportContext .= "Credit Scores:\n";
                foreach ($latestReport->creditScores as $score) {
                    $creditReportContext .= "- {$score->bureau}: {$score->score} ({$score->lender_rank})\n";
                }
                $creditReportContext .= "\n";
            }
            
            // Account Summary
            $totalAccounts = $latestReport->creditAccounts->count();
            $openAccounts = $latestReport->creditAccounts->where('account_status', 'Open')->count();
            $creditReportContext .= "Accounts Summary:\n";
            $creditReportContext .= "- Total Accounts: {$totalAccounts}\n";
            $creditReportContext .= "- Open Accounts: {$openAccounts}\n\n";
            
            // Inquiries
            $totalInquiries = $latestReport->creditInquiries->count();
            $creditReportContext .= "Hard Inquiries: {$totalInquiries}\n\n";
            
            $creditReportContext .= "NOTE: You can reference this data when helping the user create dispute letters. If they ask about their credit report, accounts, or inquiries, use this information.\n";
        }


        // Build system prompt from database (editable via admin panel at /admin/bot-prompts)
        $systemPrompt = "📅 Today is {$date_today}.\n{$creditReportContext}\n\n";
        
        // Load prompts from database with fallback to hard-coded if database fails
        try {
            // Determine which flow prompt to use
            if ($isAutoDispute) {
                // AUTO-DISPUTE: Use auto_dispute_prompt (generates immediately)
                $flowPrompt = \App\Models\BotPrompt::getByKey('auto_dispute_prompt');
                if ($flowPrompt) {
                    $systemPrompt .= $flowPrompt . "\n\n";
                } else {
                    // Fallback if prompt doesn't exist yet
                    $systemPrompt .= "🎯 AUTO-DISPUTE MODE:\n";
                    $systemPrompt .= "The user has provided ALL required information.\n";
                    $systemPrompt .= "DO NOT ask any questions. Generate the dispute letter IMMEDIATELY.\n\n";
                }
            } else {
                // MANUAL DISPUTE: Use manual_dispute_prompt (asks questions one-by-one)
                $flowPrompt = \App\Models\BotPrompt::getByKey('manual_dispute_prompt');
                if ($flowPrompt) {
                    $systemPrompt .= $flowPrompt . "\n\n";
                } else {
                    // Fallback if prompt doesn't exist yet - use old prompts
                    $systemPrompt .= \App\Models\BotPrompt::getByKey('system_core') . "\n\n";
                    $systemPrompt .= \App\Models\BotPrompt::getByKey('conversation_rules') . "\n\n";
                    $systemPrompt .= \App\Models\BotPrompt::getByKey('flow_steps') . "\n\n";
                }
            }
            
            // Common prompts for both flows
            $systemPrompt .= \App\Models\BotPrompt::getByKey('button_handling') . "\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('text_input_handling') . "\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('add_account_logic') . "\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('letter_output_rules') . "\n\n";
            
            // Add letter templates (these can be used by AI for reference)
            $systemPrompt .= "📌 Letter Templates:\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('template_collection_ownership') . "\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('template_collection_date_timing') . "\n\n";
            $systemPrompt .= \App\Models\BotPrompt::getByKey('template_bankruptcy') . "\n\n";
            $systemPrompt .= "**IMPORTANT:** For Bankruptcy disputes, ALWAYS use the template_bankruptcy template exactly as written. Do not modify the legal language.\n\n";

            
            // Add user information for letter generation
            $systemPrompt .= "\n\n📄 LETTER OUTPUT:\n";
            $systemPrompt .= "After confirmation, write a full dispute letter in professional tone using this structure:\n";
            $systemPrompt .= "- Use legally sound language\n";
            $systemPrompt .= "- Add citations where relevant\n\n";
            $systemPrompt .= "Enclose the entire letter in (Always include subject in the letter):\n\n";
            $systemPrompt .= "== BEGIN LETTER ==\n";
            $systemPrompt .= "{$authUser->name}\n";
            $systemPrompt .= "{$authUser->address}, {$authUser->city}, {$authUser->state} {$authUser->zipcode}\n";
            $systemPrompt .= "SSN: XXX-XX-{$authUser->ssn_last4}\n";
            $systemPrompt .= "{$date_today}\n";
            $systemPrompt .= "...\n\n";
            $systemPrompt .= "Sincerely,\n\n";
            $systemPrompt .= "{$authUser->name}\n";
            $systemPrompt .= "== END LETTER ==\n\n";
            $systemPrompt .= "🛑 AFTER GENERATING THE LETTER, ALWAYS END WITH:\n";
            $systemPrompt .= "== BEGIN METADATA ==\n";
            $systemPrompt .= "CREDITOR: [Extract the creditor/company name from the conversation]\n";
            $systemPrompt .= "BUREAU: [Extract the bureau name from the conversation]\n";
            $systemPrompt .= "== END METADATA ==\n\n";
            
            // Add guided context AFTER all other prompts to ensure it takes priority
            if (!empty($guidedContext)) {
                $systemPrompt .= "\n\n" . str_repeat("=", 80) . "\n";
                $systemPrompt .= "🚨 CRITICAL OVERRIDE INSTRUCTIONS (HIGHEST PRIORITY) 🚨\n";
                $systemPrompt .= str_repeat("=", 80) . "\n\n";
                $systemPrompt .= $guidedContext;
                $systemPrompt .= "\n" . str_repeat("=", 80) . "\n\n";
            }
            
        } catch (\Exception $e) {
            // Fallback to basic prompt if database fails
            \Log::error('Failed to load bot prompts from database: ' . $e->getMessage());
            $systemPrompt .= "You are Credit Remedi AI, a credit repair assistant. Help users create dispute letters by asking questions one at a time and generating professional letters with legal citations.";
        }
        $messages = collect(session('messages', []))
            ->filter(fn($m) => in_array($m['role'], ['user', 'assistant']))
            ->values()
            ->all();

        // If we have guided context with dispute reason, inject it as a system message
        // This makes the AI think the user already answered the dispute reason question
        if (!empty($guidedContext) && isset($conversationState['data']['dispute_reason'])) {
            $disputeReason = $conversationState['data']['dispute_reason'];
            $messages[] = [
                'role' => 'system',
                'content' => "NOTE: The user has already provided the dispute reason: '{$disputeReason}'. Do NOT ask for the dispute reason again. Proceed with other questions.",
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $validated['message'],
            'timestamp' => now()->toIso8601String(),
        ];

        $apiMessages = $messages;
        array_unshift($apiMessages, [
            'role' => 'system',
            'content' => $systemPrompt
        ]);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => collect($apiMessages)->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])->toArray(), // Clean timestamp out
            'temperature' => 0.7,
        ]);

        $aiReply = $response->json('choices.0.message.content') ?? 'Sorry, I didn’t get that. Please try again.';

        
        

        $messages[] = [
            'role' => 'assistant',
            'content' => $aiReply,
            'timestamp' => now()->toIso8601String(),
        ];
        
        // Helper function to extract details from AI response
        function extractDetail($text, $label) {
            $escapedLabel = preg_quote($label, '/'); // escape special characters
            $pattern = "/{$escapedLabel}:\s*(.*?)(?:\r?\n|$)/i"; // non-greedy match until newline or end of string
            preg_match($pattern, $text, $matches);
            return $matches[1] ?? null;
        }

        try {
            

            if (Str::contains($aiReply, '== BEGIN LETTER ==') && Str::contains($aiReply, '== END LETTER ==')) {
            $letterOnly = trim(Str::between($aiReply, '== BEGIN LETTER ==', '== END LETTER =='));

            // Extract structured info (assumes they appear *before* the letter or in same message)
            
            $creditItemType      = extractDetail($aiReply, 'Type of Credit Item');
            $creditBureau        = extractDetail($aiReply, 'Credit Bureau');
            $accountNumber       = extractDetail($aiReply, 'Account Number');
            $disputeReason       = extractDetail($aiReply, 'Reason for Dispute');
            $desiredResolution   = extractDetail($aiReply, 'Desired Resolution');
            
            // Try to extract creditor from metadata first
            $creditorName = null;
            if (Str::contains($aiReply, '== BEGIN METADATA ==')) {
                $metadata = Str::between($aiReply, '== BEGIN METADATA ==', '== END METADATA ==');
                $creditorName = extractDetail($metadata, 'CREDITOR');
            }
            
            // For auto-disputes, extract from user message (e.g., "Creditor: SELFRENT")
            if (!$creditorName && $isAutoDispute) {
                if (preg_match('/Creditor:\s*(.+?)(?:\n|$)/i', $validated['message'], $matches)) {
                    $creditorName = trim($matches[1]);
                }
            }
            
            // Fallback: extract from letter content if not in metadata
            if (!$creditorName) {
                $creditorName = extractDetail($aiReply, 'Creditor/Company Name');
            }
            
            // Try **Creditor:** pattern
            if (!$creditorName && preg_match('/\*\*Creditor:\*\*\s*(.+?)(?:\n|$)/i', $letterOnly, $matches)) {
                $creditorName = trim($matches[1]);
            }
            
            // Try natural language: "creditor KLARNA" or "creditor name KLARNA"
            // Try natural language: "creditor KLARNA" or "creditor name KLARNA"
            if (!$creditorName && preg_match('/(?:the\s+)?creditor(?:\s+name)?\s+([A-Z][A-Z0-9\s&\-\.]+?)(?:\.|,|\s+The\s+account)/i', $letterOnly, $matches)) {
                $creditorName = trim($matches[1]);
            }

            // --- HISTORY FALLBACK (Deep Search) ---
            // If we still don't have it, look at the chat history for the answer to the creditor question
            if (!$creditorName) {
                $history = session('messages', []);
                // Iterate backwards
                for ($i = count($history) - 1; $i >= 0; $i--) {
                    $msg = $history[$i];
                    if ($msg['role'] === 'assistant' && 
                        (str_contains(strtolower($msg['content']), 'name of the creditor') || 
                         str_contains(strtolower($msg['content']), 'creditor or collection agency'))) {
                        
                        // The NEXT message (forward in time) or the one *after* this in the array (reversed index logic)
                        // Wait, we are iterating backwards. The user's answer would be at $i + 1 (if chronological) 
                        // But $history is appended. So user answer is $i + 1.
                        
                        if (isset($history[$i + 1]) && $history[$i + 1]['role'] === 'user') {
                             $possibleCreditor = trim($history[$i + 1]['content']);
                             // filter out simple confirmation words just in case
                             if (!in_array(strtolower($possibleCreditor), ['yes', 'no', 'generate', 'continue'])) {
                                 $creditorName = $possibleCreditor;
                                 break;
                             }
                        }
                    }
                }
            }

            // --- MANUAL FLOW VARIABLE FALLBACK ---
            // Always try to pull from session data if variables are missing
            $sessionData = $conversationState['data'] ?? [];
            
            if (!$creditorName && !empty($sessionData['creditor'])) {
                $creditorName = $sessionData['creditor'];
            }
            
            if (!$accountNumber && !empty($sessionData['account_number'])) {
                $accountNumber = $sessionData['account_number'];
            }
            
            if (!$creditItemType && !empty($sessionData['account_type'])) {
                $creditItemType = $sessionData['account_type'];
            }
            
            if (!$creditBureau && !empty($sessionData['bureau'])) {
                $creditBureau = $sessionData['bureau'];
            }
            
            if (!$disputeReason && !empty($sessionData['dispute_reason'])) {
                $disputeReason = $sessionData['dispute_reason'];
            }

            // Define the 3 credit bureaus with their addresses
            // Define the 3 credit bureaus with their addresses (Dynamic from DB)
            $bureaus = \App\Models\BureauAddress::active()
                ->get()
                ->keyBy('name')
                ->map(fn($b) => [
                    'name' => $b->name,
                    'address' => $b->full_address
                ])
                ->toArray();

            // Fallback for Secondary Bureaus if not in DB
            $secondaryDefaults = [
                'Innovis' => "Innovis Consumer Assistance\nP.O. Box 1682\nPittsburgh, PA 15230-1682",
                'LexisNexis' => "LexisNexis Consumer Center\nP.O. Box 105108\nAtlanta, GA 30348-5108",
                'ChexSystems' => "ChexSystems Consumer Relations\n7805 Hudson Road, Suite 100\nWoodbury, MN 55125",
                'ARS' => "ARS Consumer Relations\nP.O. Box 469046\nEscondido, CA 92046",
                'Sagestream' => "SageStream, LLC Consumer Office\nP.O. Box 503793\nSan Diego, CA 92150",
                'CoreLogic' => "CoreLogic Credco Consumer Services Department\nP.O. Box 509124\nSan Diego, CA 92150",
                'Clarity Services' => "Clarity Services, Inc.\nP.O. Box 5717\nClearwater, FL 33758",
            ];

            foreach ($secondaryDefaults as $name => $address) {
                // If not already in DB list (case insensitive check)
                $exists = collect($bureaus)->keys()->contains(fn($k) => strcasecmp($k, $name) === 0);
                if (!$exists) {
                    $bureaus[$name] = [
                        'name' => $name,
                        'address' => $address
                    ];
                }
            }

            $savedLetters = [];
            
            // Check if user selected all 3 bureaus or a specific one
            // Look for the bureau selection in the conversation history
            $selectedBureau = null;
            $generateAll3 = false;
            
            // Check session state for bureau info
            $botState = session('bot_state', []);
            if (isset($botState['data']['generate_3_letters']) && $botState['data']['generate_3_letters']) {
                $generateAll3 = true;
            } elseif (isset($botState['data']['bureau'])) {
                $selectedBureau = $botState['data']['bureau'];
                if ($selectedBureau === 'All 3 Bureaus') {
                    $generateAll3 = true;
                }
            }
            
            // For auto-disputes, try to extract bureau from the message text
            if ($isAutoDispute && !$selectedBureau && !$generateAll3) {
                // Parse "Bureau: Experian" or "Bureau: TransUnion, Experian, Equifax"
                if (preg_match('/Bureau:\s*(.+?)(?:\n|$)/i', $validated['message'], $matches)) {
                    $bureauText = trim($matches[1]);
                    
                    // Check if it's multiple bureaus (contains commas or "and")
                    if (str_contains($bureauText, ',') || str_contains($bureauText, ' and ')) {
                        // Multiple bureaus - generate for all mentioned
                        $generateAll3 = true;
                    } else {
                        // Single bureau - use the exact name
                        $selectedBureau = $bureauText;
                    }
                }
            }
            
            // If we still don't know, try to extract from credit bureau metadata
            if (!$generateAll3 && !$selectedBureau && $creditBureau) {
                $selectedBureau = $creditBureau;
            }
            
            // Determine which bureaus to generate letters for
            // Determine which bureaus to generate letters for
            $bureausToGenerate = [];
            if ($generateAll3) {
                // Filter to only include the Big 3
                $bureausToGenerate = collect($bureaus)
                    ->filter(function($value, $key) {
                        return in_array(strtolower($key), ['equifax', 'experian', 'transunion']);
                    })
                    ->toArray();
            } elseif ($selectedBureau) {
                // Case-insensitive lookup using DB keys
                $bureauKey = collect($bureaus)->keys()->first(fn($k) => strcasecmp($k, $selectedBureau) === 0);
                
                if ($bureauKey && isset($bureaus[$bureauKey])) {
                    $bureausToGenerate = [$bureauKey => $bureaus[$bureauKey]]; // Just one
                } else {
                    // Fallback: generate for all 3 if selected but not found (safety)
                    $bureausToGenerate = collect($bureaus)
                        ->filter(function($value, $key) {
                            return in_array(strtolower($key), ['equifax', 'experian', 'transunion']);
                        })
                        ->toArray();
                }
            } else {
                // Fallback: generate for all 3 if we can't determine
                $bureausToGenerate = collect($bureaus)
                    ->filter(function($value, $key) {
                        return in_array(strtolower($key), ['equifax', 'experian', 'transunion']);
                    })
                    ->toArray();
            }
            
            // Generate letters for selected bureau(s)
            foreach ($bureausToGenerate as $bureauKey => $bureauInfo) {
                // Customize letter for this specific bureau
                $customizedLetter = $letterOnly;
                
                // Replace ALL placeholders with actual user data
                // Name
                $customizedLetter = str_replace('[Your Name]', $authUser->name, $customizedLetter);
                $customizedLetter = str_replace('[User Name]', $authUser->name, $customizedLetter);
                
                // Address
                $userFullAddress = "{$authUser->address}, {$authUser->city}, {$authUser->state} {$authUser->zipcode}";
                $customizedLetter = str_replace('[Your Address]', $userFullAddress, $customizedLetter);
                $customizedLetter = str_replace('[Address]', $userFullAddress, $customizedLetter);
                $customizedLetter = str_replace('[User Address]', $userFullAddress, $customizedLetter);
                
                // City/State/Zip specific
                $customizedLetter = str_replace('[City, State, ZIP Code]', "{$authUser->city}, {$authUser->state}, {$authUser->zipcode}", $customizedLetter);
                $customizedLetter = str_replace('[City, State, ZIP]', "{$authUser->city}, {$authUser->state}, {$authUser->zipcode}", $customizedLetter);
                
                // Email
                $customizedLetter = str_replace('[Your Email Address]', $authUser->email, $customizedLetter);
                $customizedLetter = str_replace('[Email]', $authUser->email, $customizedLetter);
                
                // Date
                $todayDate = now()->format('F j, Y');
                $customizedLetter = str_replace('[Date]', $todayDate, $customizedLetter);
                $customizedLetter = str_replace("[Today's Date]", $todayDate, $customizedLetter);
                $customizedLetter = str_replace('[Current Date]', $todayDate, $customizedLetter);
                $customizedLetter = str_replace('[City, State, ZIP Code]', "{$authUser->city}, {$authUser->state}, {$authUser->zipcode}", $customizedLetter);
                $customizedLetter = str_replace('[Your Email Address]', $authUser->email, $customizedLetter);
                
                // Replace generic bureau placeholders with specific bureau info
                $customizedLetter = str_replace('[Bureau]', $bureauInfo['name'], $customizedLetter);
                $customizedLetter = str_replace('[Bureau Name]', $bureauInfo['name'], $customizedLetter);
                $customizedLetter = str_replace('[Credit Bureau]', $bureauInfo['name'], $customizedLetter);
                $customizedLetter = str_replace('[Credit Bureau Name]', $bureauInfo['name'], $customizedLetter);
                $customizedLetter = str_replace('Dear [Credit Bureau Name]', "Dear {$bureauInfo['name']}", $customizedLetter);
                
                // Remove duplicate bureau address section (the placeholder one that appears after the real one)
                // Pattern: [Credit Bureau Name]\n[Credit Bureau Address]\n[City, State, ZIP Code]
                $customizedLetter = preg_replace('/\[Credit Bureau Name\].*?\[City, State, ZIP Code\]\s*/s', '', $customizedLetter);
                
                // Remove ANY existing bureau address to prevent duplicates
                // This handles cases where AI already included the address
                $bureauAddressLines = explode("\n", $bureauInfo['address']);
                foreach ($bureauAddressLines as $addressLine) {
                    $addressLine = trim($addressLine);
                    if (!empty($addressLine)) {
                        // Remove the line if it exists (case-insensitive, flexible whitespace)
                        $pattern = '/' . preg_quote($addressLine, '/') . '\s*/i';
                        $customizedLetter = preg_replace($pattern, '', $customizedLetter);
                    }
                }
                
                // AGGRESSIVE CLEANING: Remove all bureau info to start fresh
                
                // Remove generic headers
                $customizedLetter = preg_replace('/Credit Reporting Agency.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/ATTN:\s*Disputes?\s*Department.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/To Whom It May Concern,?\n/i', '', $customizedLetter); // Remove salutation (we might add it back later if needed, but safer to strip dups)
                
                // Remove specific bureau blocks (Name + Address patterns)
                
                // Equifax
                $customizedLetter = preg_replace('/Equifax.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*7402[0-9]{2}.*?\n/i', '', $customizedLetter); // Covers 740241, 740256
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*105314.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/Atlanta,?\s*GA.*?\n/i', '', $customizedLetter);
                
                // Experian
                $customizedLetter = preg_replace('/Experian.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*9701.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*4500.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/Allen,?\s*TX.*?\n/i', '', $customizedLetter);
                
                // TransUnion
                $customizedLetter = preg_replace('/TransUnion.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*2000.*?\n/i', '', $customizedLetter);
                $customizedLetter = preg_replace('/Chester,?\s*PA.*?\n/i', '', $customizedLetter);
                
                // Remove standalone P.O. Boxes if any remain
                $customizedLetter = preg_replace('/P\.?O\.?\s*Box\s*\d+.*?\n/i', '', $customizedLetter);
                
                // Clean up multiple consecutive newlines
                $customizedLetter = preg_replace('/\n{3,}/', "\n\n", $customizedLetter);
                
                // Now add the bureau address ONCE in the correct position (after user info)
                $lines = explode("\n", $customizedLetter);
                $insertPosition = 0;
                
                // Find the line after date (usually line 5-7)
                foreach ($lines as $index => $line) {
                    if (preg_match('/\d{4}$/', trim($line)) || // Ends with year
                        preg_match('/\d{2}\/\d{2}\/\d{4}/', $line) || // Date format
                        preg_match('/Date:/i', $line)) { // Explicit Date: prefix
                        $insertPosition = $index + 1;
                        break;
                    }
                }
                
                // Fallback: If no date found, insert after line 5 (likely after user header)
                if ($insertPosition == 0 && count($lines) > 5) {
                    $insertPosition = 6;
                }

                // Insert bureau address (single line, no extra blank lines)
                $bureauFullAddress = str_replace(["\r\n", "\n", "\r"], ", ", trim($bureauInfo['address']));
                if ($insertPosition > 0 && $insertPosition < count($lines)) {
                    array_splice($lines, $insertPosition, 0, [$bureauFullAddress]);
                    $customizedLetter = implode("\n", $lines);
                } else {
                    // unexpected short letter, just append to top
                     $customizedLetter = $bureauFullAddress . "\n" . $customizedLetter;
                }

                
                // Save letter for this bureau
                $savedLetter = DisputeLetter::create([
                    'user_id'              => auth()->id(),
                    'letter_content'       => $customizedLetter,
                    'credit_bureau'        => $bureauInfo['name'],
                    'credit_item_type'     => $creditItemType,
                    'creditor_name'        => $creditorName,
                    'account_number'       => $accountNumber,
                    'dispute_reason'       => $disputeReason,
                    'desired_resolution'   => $desiredResolution,
                ]);
                
                $savedLetters[] = $savedLetter;
            }
            
            // Add a follow-up message
            $lettersList = "✅ **Your dispute letter" . (count($savedLetters) > 1 ? "s have" : " has") . " been generated!**\n\n";
            if (count($savedLetters) > 1) {
                $lettersList .= "We've created **" . count($savedLetters) . " separate letters** for you:\n\n";
            }
            foreach ($savedLetters as $letter) {
                $lettersList .= "📄 **{$letter->credit_bureau}** - Ready to send\n";
            }
            $lettersList .= "\n📋 **Find and manage your letters in the Disputes tab.**";
            
            $messages[] = [
                'role' => 'assistant',
                'content' => $lettersList,
                'type' => 'options',
                'options' => [
                    ['label' => '📋 View My Disputes', 'value' => 'View Disputes'],
                    ['label' => '➕ Create Another Letter', 'value' => 'Create Dispute Letter'],
                ],
                'timestamp' => now()->toIso8601String(),
            ];
        }
        } catch (\Exception $e) {
            Log::error('Failed to save dispute letter: ' . $e->getMessage());
        }

        session(['messages' => $messages]);

        // Always return JSON for chat endpoint with structured format
        return response()->json([
            'success' => true,
            'message' => $aiReply,
            'type' => 'text', // AI responses default to text type
            'options' => [], // AI responses don't have predefined options
            'messages' => $messages
        ]);

        
        
    }


    public function upload(Request $request)
    {
        $request->validate([
            'credit_file' => 'required|file|max:15360', // 15MB max
        ]);

        $file = $request->file('credit_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('credit_reports', $filename, 'public');
        $fullPath = Storage::disk('public')->path($path);

        $parsedData = [
            'personal_info' => [
                'first_name' => auth()->user()->name,
                'last_name' => '',
                'date_of_birth' => '',
                'current_address' => '',
                'identifiers' => ['none'],
            ],
            'accounts' => [],
            'inquiries' => [],
            'score' => 'unknown',
        ];

        try {
            if ($ext === 'html' || $ext === 'htm') {
                // HTML Parser
                $html = file_get_contents($fullPath);
                $parser = new IdentityIQParserService($html);
                $res = $parser->parseAll();
                
                $parsedData['personal_info'] = [
                    'first_name' => $res['personal_info']['first_name'] ?? auth()->user()->name,
                    'last_name' => $res['personal_info']['last_name'] ?? '',
                    'date_of_birth' => $res['personal_info']['date_of_birth'] ?? '',
                    'current_address' => $res['personal_info']['current_address'] ?? '',
                    'identifiers' => $res['personal_info']['identifiers'] ?? ['none'],
                ];

                foreach ($res['accounts'] as $acc) {
                    if ($acc['is_collection'] || $acc['is_chargeoff'] || $acc['is_repo'] || $acc['has_lates']) {
                        $parsedData['accounts'][] = [
                            'creditor_name' => $acc['creditor_name'],
                            'account_number' => $acc['account_number'],
                            'account_type' => $acc['is_collection'] ? 'collection' : ($acc['is_chargeoff'] ? 'charge-off' : ($acc['is_repo'] ? 'repossession' : 'late payment')),
                            'balance' => $acc['balance'],
                            'bureau' => $acc['bureau'] ?? 'All',
                            'dispute_reason' => 'Incorrect information, please verify validity and accuracy of reporting details.',
                        ];
                    }
                }

                foreach ($res['inquiries'] as $inq) {
                    $parsedData['inquiries'][] = [
                        'creditor_name' => $inq['creditor_name'],
                        'inquiry_date' => $inq['inquiry_date'] ?? 'N/A',
                        'bureau' => $inq['bureau'] ?? 'All',
                        'dispute_reason' => 'No permissible purpose / unauthorized inquiry.',
                    ];
                }

                // Get first score
                if (!empty($res['credit_scores'])) {
                    $parsedData['score'] = $res['credit_scores'][0]['score'] >= 740 ? '740plus' : ($res['credit_scores'][0]['score'] >= 670 ? '670_739' : ($res['credit_scores'][0]['score'] >= 580 ? '580_669' : 'sub580'));
                }

            } elseif (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                // Vision AI
                $extractor = new AIVisionExtractorService();
                $res = $extractor->extractFromImage($file);
                
                if (!$res['success'] || !($res['data']['is_valid_report'] ?? false)) {
                    return back()->with('error', 'The uploaded image does not appear to be a valid credit report screenshot. Please upload a clear screenshot of your credit details.');
                }

                $data = $res['data'];
                $parsedData['personal_info'] = [
                    'first_name' => $data['personal_info']['first_name'] ?? auth()->user()->name,
                    'last_name' => $data['personal_info']['last_name'] ?? '',
                    'date_of_birth' => $data['personal_info']['date_of_birth'] ?? '',
                    'current_address' => $data['personal_info']['current_address'] ?? '',
                    'identifiers' => ['none'],
                ];
                $parsedData['score'] = $data['score'] ?? 'unknown';

                foreach ($data['accounts'] ?? [] as $acc) {
                    if (($acc['account_status'] ?? '') === 'negative' || ($acc['account_type'] ?? '') !== 'positive') {
                        $parsedData['accounts'][] = [
                            'creditor_name' => $acc['creditor_name'],
                            'account_number' => $acc['account_number'] ?? 'XXXX',
                            'account_type' => $acc['account_type'] ?? 'collection',
                            'balance' => $acc['balance'] ?? 0,
                            'bureau' => $acc['bureau'] ?? 'All',
                            'dispute_reason' => 'Incorrect information, please verify validity and accuracy of reporting details.',
                        ];
                    }
                }

                foreach ($data['inquiries'] ?? [] as $inq) {
                    $parsedData['inquiries'][] = [
                        'creditor_name' => $inq['creditor_name'],
                        'inquiry_date' => $inq['inquiry_date'] ?? 'N/A',
                        'bureau' => $inq['bureau'] ?? 'All',
                        'dispute_reason' => 'No permissible purpose / unauthorized inquiry.',
                    ];
                }

            } elseif ($ext === 'pdf') {
                // PDF Text Extraction
                try {
                    $text = (new SpatiePdf())->setPdf($fullPath)->text();
                } catch (\Exception $e) {
                    $parser = new SmalotParser();
                    $pdf = $parser->parseFile($fullPath);
                    $text = $pdf->getText();
                }

                // AI extraction
                $extractor = new AIReportExtractorService();
                $res = $extractor->extractFromText($text);

                if (!empty($res['personal_info'])) {
                    $parsedData['personal_info'] = [
                        'first_name' => $res['personal_info']['first_name'] ?? auth()->user()->name,
                        'last_name' => $res['personal_info']['last_name'] ?? '',
                        'date_of_birth' => $res['personal_info']['date_of_birth'] ?? '',
                        'current_address' => $res['personal_info']['current_address'] ?? '',
                        'identifiers' => ['none'],
                    ];
                }

                foreach ($res['accounts'] ?? [] as $acc) {
                    $status = strtolower($acc['account_status'] ?? '');
                    if ($status === 'negative' || $status === 'collection' || $status === 'charge-off') {
                        $parsedData['accounts'][] = [
                            'creditor_name' => $acc['creditor_name'],
                            'account_number' => $acc['account_number'] ?? 'XXXX',
                            'account_type' => $acc['account_type'] ?? 'collection',
                            'balance' => $acc['balance'] ?? 0,
                            'bureau' => $acc['bureau'] ?? 'All',
                            'dispute_reason' => 'Incorrect information, please verify validity and accuracy of reporting details.',
                        ];
                    }
                }

                foreach ($res['inquiries'] ?? [] as $inq) {
                    $parsedData['inquiries'][] = [
                        'creditor_name' => $inq['creditor_name'],
                        'inquiry_date' => $inq['inquiry_date'] ?? 'N/A',
                        'bureau' => $inq['bureau'] ?? 'All',
                        'dispute_reason' => 'No permissible purpose / unauthorized inquiry.',
                    ];
                }
            }

            session(['parsed_credit_data' => $parsedData]);
            return redirect()->route('credit-reports.review');

        } catch (\Exception $e) {
            Log::error('Upload/parse failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to parse credit file: ' . $e->getMessage());
        }
    }

    public function showReviewScreen()
    {
        $parsedData = session('parsed_credit_data');
        if (!$parsedData) {
            return redirect()->route('credit-reports.uploadPage')->with('error', 'Please upload a credit report first.');
        }

        return view('credit-reports.review', compact('parsedData'));
    }

    public function saveReviewData(Request $request)
    {
        $user = auth()->user();
        
        // Retrieve and format confirmed items from request
        $personalInfo = $request->input('personal_info', []);
        $accounts = $request->input('accounts', []);
        $inquiries = $request->input('inquiries', []);

        $confirmedData = [
            'personal_info' => $personalInfo,
            'accounts' => $accounts,
            'inquiries' => $inquiries,
        ];

        // Store user address details if extracted
        if (!empty($personalInfo['current_address']) && empty($user->address)) {
            $user->update([
                'address' => $personalInfo['current_address'],
                'date_of_birth' => $personalInfo['date_of_birth'] ?? $user->date_of_birth,
            ]);
        }

        // Calculate score & findings
        $auditService = new AuditService();
        $score = $auditService->computeScore([
            'score' => session('parsed_credit_data.score', 'unknown'),
            'negatives' => array_column($accounts, 'account_type'),
            'identifiers' => $personalInfo['identifiers'] ?? [],
        ]);

        // Save CreditReport db record
        CreditReport::create([
            'user_id' => $user->id,
            'original_filename' => 'Verified Credit Report',
            'file_path' => 'N/A',
            'extracted_text' => json_encode($confirmedData),
            'personal_info' => $personalInfo,
            'total_accounts_count' => count($accounts),
            'open_accounts_count' => 0,
            'negative_accounts_count' => count($accounts),
            'hard_inquiries_count' => count($inquiries),
        ]);

        // Save user credit score
        foreach (['EQF', 'EXP', 'TUC'] as $bureauCode) {
            DB::table('credit_scores')->insert([
                'user_id' => $user->id,
                'bureau' => $bureauCode,
                'score' => $score,
                'score_model' => 'VantageScore',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate letters
        $letterGenerator = new PhasedLetterGenerator();
        $res = $letterGenerator->generateLettersForUser($user, $confirmedData);

        // Clear session data
        session()->forget('parsed_credit_data');

        if ($res['success']) {
            return redirect()->route('credit-repair-bot')->with('success', 'Your Credit Game Plan and ' . $res['count'] . ' dispute letters have been created successfully!');
        } else {
            return redirect()->route('credit-repair-bot')->with('error', 'Your Game Plan was created, but letter generation failed: ' . $res['error']);
        }
    }

    public function showQuestionnaire()
    {
        return view('credit-reports.questionnaire');
    }

    public function saveQuestionnaireData(Request $request)
    {
        $user = auth()->user();
        
        $answers = $request->validate([
            'goal' => 'required|string',
            'identifiers' => 'array',
            'negatives' => 'array',
            'co_count' => 'nullable|string',
            'co_status' => 'nullable|string',
            'late_count' => 'nullable|string',
            'utilization' => 'required|string',
            'mix' => 'required|string',
            'score' => 'required|string',
        ]);

        // Generate confirmed data structure based on manual quiz answers
        $personalInfo = [
            'first_name' => $user->name,
            'last_name' => '',
            'identifiers' => $answers['identifiers'] ?? ['none'],
        ];

        $accounts = [];
        $negatives = $answers['negatives'] ?? [];

        if (in_array('collections', $negatives)) {
            $accounts[] = [
                'creditor_name' => 'Collection Agency',
                'account_number' => 'XXXX',
                'account_type' => 'collection',
                'balance' => 350.00,
                'bureau' => 'All',
                'dispute_reason' => 'I am disputing the validity of this collection account, please provide proof of authorization.'
            ];
        }

        if (in_array('chargeoffs', $negatives)) {
            $accounts[] = [
                'creditor_name' => 'Original Creditor (Charge-Off)',
                'account_number' => 'XXXX',
                'account_type' => 'charge-off',
                'balance' => 1200.00,
                'bureau' => 'All',
                'dispute_reason' => 'Incorrect accounting, please verify reported balance and payment records.'
            ];
        }

        if (in_array('repo', $negatives)) {
            $accounts[] = [
                'creditor_name' => 'Auto Finance Company (Reposcession)',
                'account_number' => 'XXXX',
                'account_type' => 'repossession',
                'balance' => 8500.00,
                'bureau' => 'All',
                'dispute_reason' => 'I am requesting full verification of the deficiency balance calculations following the resale.'
            ];
        }

        if (in_array('lates', $negatives)) {
            $accounts[] = [
                'creditor_name' => 'Credit Account (Late Payments)',
                'account_number' => 'XXXX',
                'account_type' => 'late payment',
                'balance' => 0.00,
                'bureau' => 'All',
                'dispute_reason' => 'I am disputing the late payment notations reported for this account, please correct.'
            ];
        }

        $inquiries = [];
        if (in_array('inquiries', $negatives)) {
            $inquiries[] = [
                'creditor_name' => 'Credit Pull Agency',
                'inquiry_date' => now()->subMonths(3)->format('m/d/Y'),
                'bureau' => 'All',
                'dispute_reason' => 'Unauthorized inquiry without permissible purpose.'
            ];
        }

        $confirmedData = [
            'personal_info' => $personalInfo,
            'accounts' => $accounts,
            'inquiries' => $inquiries,
        ];

        // Audit score
        $auditService = new AuditService();
        $score = $auditService->computeScore($answers);

        // Save report & scores
        CreditReport::create([
            'user_id' => $user->id,
            'original_filename' => 'Manual Onboarding Quiz',
            'file_path' => 'N/A',
            'extracted_text' => json_encode($confirmedData),
            'personal_info' => $personalInfo,
            'total_accounts_count' => count($accounts),
            'open_accounts_count' => 0,
            'negative_accounts_count' => count($accounts),
            'hard_inquiries_count' => count($inquiries),
        ]);

        foreach (['EQF', 'EXP', 'TUC'] as $bureauCode) {
            DB::table('credit_scores')->insert([
                'user_id' => $user->id,
                'bureau' => $bureauCode,
                'score' => $score,
                'score_model' => 'VantageScore',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate letters
        $letterGenerator = new PhasedLetterGenerator();
        $res = $letterGenerator->generateLettersForUser($user, $confirmedData);

        if ($res['success']) {
            return redirect()->route('credit-repair-bot')->with('success', 'Your Credit Game Plan and ' . $res['count'] . ' dispute letters have been created successfully!');
        } else {
            return redirect()->route('credit-repair-bot')->with('error', 'Your Game Plan was created, but letter generation failed: ' . $res['error']);
        }
    }

    public function showDashboard(Request $request)
    {
        $user = auth()->user();

        // Check if user has dispute letters
        $letters = DisputeLetter::where('user_id', $user->id)->get();

        if ($letters->isEmpty()) {
            // Check if user has uploaded a report
            $hasReport = CreditReport::where('user_id', $user->id)->exists();
            if (!$hasReport) {
                return redirect()->route('credit-reports.uploadPage')->with('message', 'Welcome! Please upload your credit report or continue with questions to get started.');
            }
        }

        // Parse audit report data to build dynamic recommendations and scores
        $report = CreditReport::where('user_id', $user->id)->latest()->first();
        $auditScore = 70; // fallback
        $findings = [];
        $complexity = 'low';
        $scoreLabel = ['Strengthening Phase', '#D69A2D'];

        if ($report) {
            $reportData = json_decode($report->extracted_text, true);
            if ($reportData) {
                $auditService = new AuditService();
                
                // Fetch saved score
                $savedScore = DB::table('credit_scores')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->value('score');

                $auditScore = $savedScore ?: 70;

                // Reconstruct answers array for findings
                $negTypes = array_column($reportData['accounts'] ?? [], 'account_type');
                if (!empty($reportData['inquiries'] ?? [])) {
                    $negTypes[] = 'inquiries';
                }
                
                $answers = [
                    'negatives' => $negTypes,
                    'identifiers' => $reportData['personal_info']['identifiers'] ?? [],
                    'score' => $auditScore >= 740 ? '740plus' : ($auditScore >= 670 ? '670_739' : ($auditScore >= 580 ? '580_669' : 'sub580')),
                ];

                $findings = $auditService->buildFindings($answers);
                $complexity = $auditService->computeComplexity($answers, $findings);
                $scoreLabel = $auditService->getScoreLabel($auditScore);
            }
        }

        // Group letters by Phase
        $phases = [
            1 => $letters->where('phase', 1),
            2 => $letters->where('phase', 2),
            3 => $letters->where('phase', 3),
        ];

        return view('disputes-dashboard', compact('user', 'phases', 'auditScore', 'scoreLabel', 'findings', 'complexity'));
    }

    // Show single report
    public function showReport($id)
    {
        $report = CreditReport::findOrFail($id);

        if (auth()->user()->role !== 'admin' && $report->user_id !== auth()->id()) {
            abort(403);
        }

        return view('credit-reports.show', compact('report'));
    }

    // Generate Action Plan
    public function generateActionPlan(Request $request, $id)
    {
        $report = CreditReport::findOrFail($id);

        if (auth()->user()->role !== 'admin' && $report->user_id !== auth()->id()) {
            abort(403);
        }

        $text = $report->extracted_text;
        if (!$text) {
            return response()->json(['error' => 'No extracted text found'], 400);
        }

        $prompt = <<<EOT
        You are CreditRemedi, an AI that assists with credit repair.

        Review the CREDIT REPORT CONTENT below and produce a structured action plan.

        CREDIT REPORT CONTENT:
        -----------------
        $text
        -----------------

        Format the output like this:

        ### Personal Info Flags
        - Names: ...
        - Addresses: ...
        - SSN: ...
        - DOB: ...

        ### Accounts Review (Table)
        Creditor | Type | Issue Detected | Action | Escalation

        ### Public Records
        - [summary]

        ### Inquiries
        - [summary]

        ### Final Plan of Action
        - Step 1: ...
        - Step 2: ...
        EOT;

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional credit repair assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
            ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'OpenAI API error: '.$response->body()], 500);
        }

        $data = $response->json();
        $plan = $data['choices'][0]['message']['content'] ?? 'No action plan returned';

        $report->action_plan = $plan;
        $report->action_plan_ts = now();
        $report->save();

        return redirect()
            ->route('credit-reports.show', $report->id)
            ->with('success', 'Action plan generated successfully.');

        
    }

}
