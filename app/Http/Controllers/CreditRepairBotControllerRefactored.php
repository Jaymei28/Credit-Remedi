<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * REFACTORED CHATBOT CONTROLLER WITH GUIDED CONVERSATION (QUICK REPLIES)
 * 
 * This demonstrates the pattern for structured API responses that support
 * clickable button/chip interfaces in the frontend.
 */
class CreditRepairBotControllerRefactored extends Controller
{
    /**
     * Main chat endpoint - returns structured responses with quick reply options
     */
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

        // Process message and get structured response
        $response = $this->processMessage($userMessage, $conversationState);

        // Update session state
        session(['bot_state' => $response['state']]);

        // Add to message history
        $messages = session('messages', []);
        
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->toIso8601String(),
        ];

        $messages[] = [
            'role' => 'assistant',
            'content' => $response['text'],
            'type' => $response['type'],
            'options' => $response['options'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ];

        session(['messages' => $messages]);

        // Return structured JSON response
        return response()->json([
            'success' => true,
            'message' => $response['text'],
            'type' => $response['type'],
            'options' => $response['options'] ?? [],
            'messages' => $messages,
            'state' => $response['state']
        ]);
    }

    /**
     * Process user message and return structured response
     */
    private function processMessage(string $message, array $state): array
    {
        $messageLower = strtolower(trim($message));
        $currentStep = $state['step'] ?? 'initial';

        // INITIAL GREETING
        if (in_array($messageLower, ['hi', 'hello', 'hey', 'start', 'yes'])) {
            return [
                'text' => "👋 Welcome to Credit Remedi AI!\n\nI'm here to help you fix your credit. What would you like to do today?",
                'type' => 'options',
                'options' => [
                    ['label' => '📊 Check Credit Score', 'value' => 'Check Credit Score'],
                    ['label' => '⚠️ Dispute Error', 'value' => 'Dispute Error'],
                    ['label' => '💰 View Pricing', 'value' => 'Pricing'],
                    ['label' => '❓ Help', 'value' => 'Help'],
                ],
                'state' => ['step' => 'main_menu', 'data' => []]
            ];
        }

        // CREATE DISPUTE LETTER - Start fresh dispute flow
        // This handles the "Click here to start disputing" button
        if (in_array($messageLower, ['create dispute letter', 'click here to start disputing', 'start disputing'])) {
            return [
                'text' => "Is this item reporting on all 3 credit bureaus (Equifax, Experian, and TransUnion)?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Yes - All 3 Bureaus', 'value' => 'Yes'],
                    ['label' => 'No - Specific Bureau', 'value' => 'No'],
                ],
                'state' => ['step' => 'ask_all_bureaus', 'data' => []] // Reset to initial state
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
                    ['label' => 'Collection', 'value' => 'Collection'],
                    ['label' => 'Charge-off', 'value' => 'Charge-off'],
                    ['label' => 'Late Payment', 'value' => 'Late Payment'],
                    ['label' => 'Bankruptcy', 'value' => 'Bankruptcy'],
                    ['label' => 'Closed Account', 'value' => 'Closed Account'],
                    ['label' => 'Personal Information', 'value' => 'Personal Information'],
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
        if ($currentStep === 'ask_bureau' && in_array($messageLower, ['equifax', 'experian', 'transunion', 'secondary bureaus'])) {
            
            // Handle Secondary Bureaus selection
            if ($messageLower === 'secondary bureaus') {
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

            $state['data']['bureau'] = $message;
            
            return [
                'text' => "What type of account or item are you disputing?",
                'type' => 'options',
                'options' => [
                    ['label' => 'Collection', 'value' => 'Collection'],
                    ['label' => 'Charge-off', 'value' => 'Charge-off'],
                    ['label' => 'Late Payment', 'value' => 'Late Payment'],
                    ['label' => 'Bankruptcy', 'value' => 'Bankruptcy'],
                    ['label' => 'Closed Account', 'value' => 'Closed Account'],
                    ['label' => 'Personal Information', 'value' => 'Personal Information'],
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
                    ['label' => 'Collection', 'value' => 'Collection'],
                    ['label' => 'Charge-off', 'value' => 'Charge-off'],
                    ['label' => 'Late Payment', 'value' => 'Late Payment'],
                    ['label' => 'Bankruptcy', 'value' => 'Bankruptcy'],
                    ['label' => 'Closed Account', 'value' => 'Closed Account'],
                    ['label' => 'Personal Information', 'value' => 'Personal Information'],
                ],
                'state' => ['step' => 'ask_account_type', 'data' => $state['data']]
            ];
        }

        // Account type selected - Ask for creditor name
        if ($currentStep === 'ask_account_type' && in_array($messageLower, ['collection', 'charge-off', 'late payment', 'bankruptcy', 'closed account', 'personal information'])) {
            $state['data']['account_type'] = $message;
            
            return [
                'text' => "What is the name of the creditor or collection agency?",
                'type' => 'text',
                'options' => [],
                'state' => ['step' => 'enter_creditor', 'data' => $state['data']]
            ];
        }

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

        // ACCOUNT ENTERED - GENERATE LETTER
        if ($currentStep === 'enter_account') {
            $state['data']['account_number'] = $message;
            
            // Here you would call OpenAI or generate the letter
            $letterPreview = $this->generateLetterPreview($state['data']);
            
            // Save state to session
            session(['bot_state' => $state]);
            
            return [
                'text' => "✅ **Dispute Letter Ready!**\n\n{$letterPreview}\n\nClick 'Generate Now' to create your dispute letter.",
                'type' => 'options',
                'options' => [
                    ['label' => '� Generate Now', 'value' => 'Generate Now'],
                    ['label' => '➕ Add Another Account', 'value' => 'Add Another'],
                    ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                ],
                'state' => ['step' => 'letter_generated', 'data' => $state['data']]
            ];
        }

        // GENERATE NOW - User clicked Generate Now button
        // Check for generate now regardless of step to ensure it works
        if ($messageLower === 'generate now') {
            $data = $state['data'] ?? [];
            $bureau = $data['bureau'] ?? 'N/A';
            $accountType = $data['account_type'] ?? 'N/A';
            $creditor = $data['creditor'] ?? 'N/A';
            $accountNumber = $data['account_number'] ?? 'N/A';
            
            // Get user info
            $user = auth()->user();
            $date_today = now()->format('F j, Y');
            
            // Define bureau addresses
            $bureauAddresses = [
                'Equifax' => "Equifax Information Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 105314\nAtlanta, GA 30348",
                'Experian' => "Experian Credit Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 9701\nAllen, TX 75013",
                'TransUnion' => "Transunion Credit Services\nCredit Reporting Agency\nATTN: Disputes Department\nPost Office Box 2000\nChester, PA 19016",
                'All 3 Bureaus' => "All Three Credit Bureaus",
                'Innovis' => "Innovis Consumer Assistance\nP.O. Box 1682\nPittsburgh, PA 15230-1682",
                'LexisNexis' => "LexisNexis Consumer Center\nP.O. Box 105108\nAtlanta, GA 30348-5108",
                'ChexSystems' => "ChexSystems Consumer Relations\n7805 Hudson Road, Suite 100\nWoodbury, MN 55125",
                'ARS' => "ARS Consumer Relations\nP.O. Box 469046\nEscondido, CA 92046",
                'Sagestream' => "SageStream, LLC Consumer Office\nP.O. Box 503793\nSan Diego, CA 92150",
                'CoreLogic' => "CoreLogic Credco Consumer Services Department\nP.O. Box 509124\nSan Diego, CA 92150",
                'Clarity Services' => "Clarity Services, Inc.\nP.O. Box 5717\nClearwater, FL 33758",
            ];
            
            $bureauAddress = $bureauAddresses[$bureau] ?? $bureau;
            
            // Generate a simple letter (you can enhance this with templates from bot_prompts)
            $letter = "{$user->name}\n";
            $letter .= "{$user->address}\n";
            $letter .= "{$user->city}, {$user->state}, {$user->zipcode}\n";
            $letter .= "{$date_today}\n\n";
            $letter .= "{$bureauAddress}\n\n";
            $letter .= "RE: Dispute of Inaccurate Information\n\n";
            $letter .= "Dear Sir/Madam,\n\n";
            $letter .= "I am writing to dispute the following information in my credit file. The items I dispute are:\n\n";
            $letter .= "Account Type: {$accountType}\n";
            $letter .= "Creditor: {$creditor}\n";
            $letter .= "Account Number: {$accountNumber}\n\n";
            $letter .= "This item is inaccurate because [reason for dispute]. I am requesting that the item be removed or corrected.\n\n";
            $letter .= "Enclosed are copies of [supporting documents] supporting my position. Please investigate this matter and correct the disputed item as soon as possible.\n\n";
            $letter .= "Sincerely,\n\n";
            $letter .= "{$user->name}";
            
            // TODO: Save to database using DisputeLetter model
            
            return [
                'text' => "✅ **Your Dispute Letter Has Been Generated!**\n\n```\n{$letter}\n```\n\nWhat would you like to do next?",
                'type' => 'options',
                'options' => [
                    ['label' => '📥 Download Letter', 'value' => 'Download'],
                    ['label' => '➕ Add Another Account', 'value' => 'Add Another'],
                    ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                ],
                'state' => ['step' => 'letter_complete', 'data' => array_merge($data, ['letter_content' => $letter])]
            ];
        }


        // CHECK CREDIT SCORE
        if ($messageLower === 'check credit score') {
            return [
                'text' => "📊 To check your credit score, you can:\n\n1. Upload your credit report\n2. Connect to IdentityIQ\n3. Manual entry\n\nWhat would you prefer?",
                'type' => 'options',
                'options' => [
                    ['label' => '📤 Upload Report', 'value' => 'Upload Report'],
                    ['label' => '🔗 Connect IdentityIQ', 'value' => 'Connect IdentityIQ'],
                    ['label' => '✍️ Manual Entry', 'value' => 'Manual Entry'],
                    ['label' => '🔙 Back', 'value' => 'Hi'],
                ],
                'state' => ['step' => 'credit_score_options', 'data' => []]
            ];
        }

        // PRICING
        if ($messageLower === 'pricing') {
            return [
                'text' => "💰 **Our Plans:**\n\n**Free** - 3 disputes/month\n**Premium** - Unlimited disputes + AI tools\n**Turbo** - Everything + priority support\n\nWould you like to upgrade?",
                'type' => 'options',
                'options' => [
                    ['label' => '⬆️ Upgrade Now', 'value' => 'Upgrade'],
                    ['label' => '📋 Compare Plans', 'value' => 'Compare Plans'],
                    ['label' => '🔙 Back', 'value' => 'Hi'],
                ],
                'state' => ['step' => 'pricing', 'data' => []]
            ];
        }

        // HELP
        if ($messageLower === 'help') {
            return [
                'text' => "❓ **How can I help you?**\n\n• Dispute errors on your credit report\n• Generate legal dispute letters\n• Track your disputes\n• Learn about credit repair\n\nJust click a button or type your question!",
                'type' => 'options',
                'options' => [
                    ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                ],
                'state' => ['step' => 'help', 'data' => []]
            ];
        }

        // DEFAULT FALLBACK - Use AI for unrecognized input
        return [
            'text' => "I'm not sure I understood that. Let me help you get started!",
            'type' => 'options',
            'options' => [
                ['label' => '🏠 Main Menu', 'value' => 'Hi'],
                ['label' => '⚠️ Dispute Error', 'value' => 'Dispute Error'],
                ['label' => '❓ Help', 'value' => 'Help'],
            ],
            'state' => ['step' => 'initial', 'data' => []]
        ];
    }

    /**
     * Generate a preview of the dispute letter
     */
    private function generateLetterPreview(array $data): string
    {
        $accountType = $data['account_type'] ?? 'N/A';
        $bureau = $data['bureau'] ?? 'N/A';
        $creditor = $data['creditor'] ?? 'N/A';
        $accountNumber = $data['account_number'] ?? 'N/A';
        
        return "**Summary:**\n" .
               "• Account Type: {$accountType}\n" .
               "• Bureau: {$bureau}\n" .
               "• Creditor: {$creditor}\n" .
               "• Account: {$accountNumber}\n\n" .
               "_Full letter will be generated with legal citations..._";
    }

    /**
     * Reset conversation
     */
    public function reset(Request $request)
    {
        session()->forget(['messages', 'bot_state']);
        
        return redirect()->route('credit-repair-bot')
            ->with('success', 'Conversation reset successfully.');
    }
}
