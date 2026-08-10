<?php

namespace App\Services;

use App\Models\DisputeLetter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhasedLetterGenerator
{
    protected $apiKey;
    protected $model = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Generate dispute letters for a user based on confirmed audit details
     */
    public function generateLettersForUser($user, array $confirmedData): array
    {
        try {
            $accounts = $confirmedData['accounts'] ?? [];
            $inquiries = $confirmedData['inquiries'] ?? [];
            $personalInfo = $confirmedData['personal_info'] ?? [];

            // Group all items by Bureau and Phase
            // Bureau keys: 'Equifax', 'Experian', 'TransUnion'
            $groups = [
                'Equifax' => [1 => [], 2 => [], 3 => []],
                'Experian' => [1 => [], 2 => [], 3 => []],
                'TransUnion' => [1 => [], 2 => [], 3 => []],
            ];

            // 1. Group Personal Info Corrections (Sent to all bureaus in Phase 1)
            $hasNameVar = !empty($personalInfo['name_variations']) && $personalInfo['name_variations'] !== 'none';
            $hasAddrVar = !empty($personalInfo['address_variations']) && $personalInfo['address_variations'] !== 'none';
            $hasEmpVar = !empty($personalInfo['employer_variations']) && $personalInfo['employer_variations'] !== 'none';

            if ($hasNameVar || $hasAddrVar || $hasEmpVar) {
                $piItem = [
                    'type' => 'personal_info',
                    'details' => [
                        'name_variations' => $personalInfo['name_variations'] ?? '',
                        'address_variations' => $personalInfo['address_variations'] ?? '',
                        'employer_variations' => $personalInfo['employer_variations'] ?? '',
                    ]
                ];
                foreach (array_keys($groups) as $bureau) {
                    $groups[$bureau][1][] = $piItem;
                }
            }

            // 2. Group Inquiries (Phase 1)
            foreach ($inquiries as $inq) {
                $bureauStr = $inq['bureau'] ?? 'All';
                $matchedBureaus = $this->parseBureaus($bureauStr);
                
                $inqItem = [
                    'type' => 'inquiry',
                    'creditor_name' => $inq['creditor_name'] ?? 'Unknown Creditor',
                    'inquiry_date' => $inq['inquiry_date'] ?? 'N/A',
                    'dispute_reason' => $inq['dispute_reason'] ?? 'Unauthorised inquiry without permissible purpose.'
                ];

                foreach ($matchedBureaus as $bureau) {
                    if (isset($groups[$bureau])) {
                        $groups[$bureau][1][] = $inqItem;
                    }
                }
            }

            // 3. Group Accounts (Phases 1, 2, or 3 based on type)
            foreach ($accounts as $acc) {
                $bureauStr = $acc['bureau'] ?? 'All';
                $matchedBureaus = $this->parseBureaus($bureauStr);

                // Assign phase based on type
                $accType = strtolower($acc['account_type'] ?? 'collection');
                $phase = 1; // Default to Phase 1 (Collections)
                
                if ($accType === 'charge-off' || $accType === 'chargeoff' || $accType === 'repossession' || $accType === 'repo') {
                    $phase = 2; // Phase 2 (Charge-offs / Repos)
                } elseif ($accType === 'late payment' || $accType === 'late_payment' || $accType === 'late') {
                    $phase = 3; // Phase 3 (Late Payments)
                }

                $accItem = [
                    'type' => 'account',
                    'account_type' => $accType,
                    'creditor_name' => $acc['creditor_name'] ?? 'Unknown Creditor',
                    'account_number' => $acc['account_number'] ?? 'N/A',
                    'balance' => $acc['balance'] ?? 0,
                    'dispute_reason' => $acc['dispute_reason'] ?? 'Incorrect information, please verify validity and accuracy of reporting details.'
                ];

                foreach ($matchedBureaus as $bureau) {
                    if (isset($groups[$bureau])) {
                        $groups[$bureau][$phase][] = $accItem;
                    }
                }
            }

            $generatedCount = 0;

            // Generate letters for each populated group
            foreach ($groups as $bureau => $phases) {
                // Get Bureau Address
                $bureauAddr = DB::table('bureau_addresses')
                    ->where('bureau_name', 'like', "%{$bureau}%")
                    ->value('address');

                if (!$bureauAddr) {
                    $bureauAddr = $this->getDefaultBureauAddress($bureau);
                }

                foreach ($phases as $phaseNum => $items) {
                    if (empty($items)) {
                        continue;
                    }

                    Log::info("Generating letter for {$bureau} - Phase {$phaseNum} (Items: " . count($items) . ")");

                    // Build prompt and generate
                    $prompt = $this->buildLetterPrompt($user, $bureau, $bureauAddr, $phaseNum, $items);
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $this->model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are an expert FCRA dispute letter writer. Create professional, polite, but firm dispute letters following consumer protection laws.'],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.2
                    ]);

                    if (!$response->ok()) {
                        throw new \Exception("OpenAI API returned error: " . $response->body());
                    }

                    $res = $response->json();
                    $content = $res['choices'][0]['message']['content'] ?? '';

                    // Clean the output content (extract between == BEGIN LETTER == and == END LETTER == if present)
                    $letterBody = $content;
                    if (str_contains($content, '== BEGIN LETTER ==')) {
                        $letterBody = trim(explode('== END LETTER ==', explode('== BEGIN LETTER ==', $content)[1] ?? $content)[0] ?? $content);
                    }

                    if (empty($letterBody)) {
                        continue;
                    }

                    // Determine main account identifiers for the database columns
                    $mainCreditor = 'Multiple Creditors';
                    $mainAccNum = 'Multiple Accounts';
                    
                    $accountItems = array_filter($items, fn($i) => $i['type'] === 'account');
                    if (count($accountItems) === 1) {
                        $single = reset($accountItems);
                        $mainCreditor = $single['creditor_name'];
                        $mainAccNum = $single['account_number'];
                    } elseif (count($items) === 1 && $items[0]['type'] === 'inquiry') {
                        $mainCreditor = $items[0]['creditor_name'];
                        $mainAccNum = 'Hard Inquiry';
                    } elseif (count($items) === 1 && $items[0]['type'] === 'personal_info') {
                        $mainCreditor = 'Personal Information Section';
                        $mainAccNum = 'Identities';
                    }

                    // Save the letter record
                    DisputeLetter::create([
                        'user_id' => $user->id,
                        'letter_content' => $letterBody,
                        'credit_bureau' => $bureau,
                        'credit_item_type' => $this->getPhaseLabel($phaseNum),
                        'creditor_name' => $mainCreditor,
                        'account_number' => $mainAccNum,
                        'dispute_reason' => 'Multiple items - see letter content',
                        'desired_resolution' => 'Delete or verify',
                        'phase' => $phaseNum,
                        'posted_1' => false,
                        'sent' => false
                    ]);

                    $generatedCount++;
                }
            }

            return [
                'success' => true,
                'count' => $generatedCount
            ];

        } catch (\Exception $e) {
            Log::error("Letter Generation Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse bureau string into individual standard bureaus
     */
    protected function parseBureaus($bureauStr): array
    {
        $bureauStr = strtolower($bureauStr);
        $found = [];
        if (str_contains($bureauStr, 'equifax') || str_contains($bureauStr, 'eqf') || str_contains($bureauStr, 'all')) {
            $found[] = 'Equifax';
        }
        if (str_contains($bureauStr, 'experian') || str_contains($bureauStr, 'exp') || str_contains($bureauStr, 'all')) {
            $found[] = 'Experian';
        }
        if (str_contains($bureauStr, 'transunion') || str_contains($bureauStr, 'tu') || str_contains($bureauStr, 'all')) {
            $found[] = 'TransUnion';
        }
        return $found;
    }

    /**
     * Get default bureau addresses if not in DB
     */
    protected function getDefaultBureauAddress($bureau): string
    {
        switch ($bureau) {
            case 'Equifax':
                return "Equifax Information Services LLC\nP.O. Box 740256\nAtlanta, GA 30374";
            case 'Experian':
                return "Experian\nP.O. Box 4500\nAllen, TX 75013";
            case 'TransUnion':
                return "TransUnion Consumer Solutions\nP.O. Box 2000\nChester, PA 19016";
            default:
                return "";
        }
    }

    /**
     * Format a descriptive label for the phase
     */
    protected function getPhaseLabel(int $phase): string
    {
        switch ($phase) {
            case 1:
                return 'Phase 1 - Personal Info & Collections';
            case 2:
                return 'Phase 2 - Charge-offs & Repossessions';
            case 3:
                return 'Phase 3 - Late Payments';
            default:
                return 'Dispute Round';
        }
    }

    /**
     * Build the structured prompt for letter generation
     */
    protected function buildLetterPrompt($user, $bureau, $bureauAddr, $phaseNum, array $items): string
    {
        $userDob = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->format('m/d/Y') : 'N/A';
        $userSsn = $user->ssn_last4 ? "XXX-XX-{$user->ssn_last4}" : 'N/A';
        $userAddr = trim("{$user->address} {$user->city} {$user->state} {$user->zipcode}");
        if (empty($userAddr)) {
            $userAddr = 'N/A';
        }

        $itemsList = '';
        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            if ($item['type'] === 'personal_info') {
                $itemsList .= "Item #{$num}: Personal Information Correction\n";
                if (!empty($item['details']['name_variations'])) {
                    $itemsList .= "  - Name variations to delete: {$item['details']['name_variations']}\n";
                }
                if (!empty($item['details']['address_variations'])) {
                    $itemsList .= "  - Outdated addresses to delete: {$item['details']['address_variations']}\n";
                }
                if (!empty($item['details']['employer_variations'])) {
                    $itemsList .= "  - Wrong employers to delete: {$item['details']['employer_variations']}\n";
                }
            } elseif ($item['type'] === 'inquiry') {
                $itemsList .= "Item #{$num}: Hard Inquiry\n";
                $itemsList .= "  - Creditor Name: {$item['creditor_name']}\n";
                $itemsList .= "  - Date: {$item['inquiry_date']}\n";
                $itemsList .= "  - Reason: {$item['dispute_reason']}\n";
            } else {
                $itemsList .= "Item #{$num}: Account Dispute ({$item['account_type']})\n";
                $itemsList .= "  - Creditor Name: {$item['creditor_name']}\n";
                $itemsList .= "  - Account Number: {$item['account_number']}\n";
                $itemsList .= "  - Reported Balance: \${$item['balance']}\n";
                $itemsList .= "  - Reason: {$item['dispute_reason']}\n";
            }
        }

        $strategyNote = '';
        if ($phaseNum === 1) {
            $strategyNote = "This is a Phase 1 dispute. If there are personal info corrections, list them clearly and request deletion. For collections or inquiries, request validation of the debt or proof of permissible purpose, citing 15 U.S.C. § 1681.";
        } elseif ($phaseNum === 2) {
            $strategyNote = "This is a Phase 2 dispute. For charge-offs or repossessions, request validation of original accounting ledger entries, reporting consistency, and check for compliance with UCC repossession notices (if applicable).";
        } elseif ($phaseNum === 3) {
            $strategyNote = "This is a Phase 3 dispute. For late payments, construct a request questioning the accuracy of specific payment history months and ask for correction or goodwill adjustment.";
        }

        return <<<EOT
Create a formal, legally compliant FCRA dispute letter sent to the following credit bureau:

BUREAU ADDRESS:
{$bureauAddr}

CONSUMER DETAILS:
Name: {$user->name}
Address: {$userAddr}
Date of Birth: {$userDob}
SSN (Last 4): {$userSsn}

DISPUTE ITEMS:
{$itemsList}

INSTRUCTIONS:
- Follow standard formal business letter layout.
- Include the Consumer Details at the top.
- List each dispute item clearly with the creditor/company name, account number/details, and the specific reason for dispute.
- Reference the Fair Credit Reporting Act (15 U.S.C. § 1681) and remind the bureau they have 30 days to investigate and verify, or delete the items.
- Keep the tone professional, direct, and serious.
- {$strategyNote}

CRITICAL: Return the letter wrapped in the delimiters "== BEGIN LETTER ==" and "== END LETTER ==" so it can be extracted. Do not add anything before or after these markers.

Example Output:
== BEGIN LETTER ==
[Your Name]
[Address]
[Date]

[Bureau Address]

Subject: Dispute of Credit Information

To Whom It May Concern,
...
== END LETTER ==
EOT;
    }
}
