<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIReportExtractorService
{
    protected $apiKey;
    protected $model = 'gpt-4o-mini'; // Mini model has 128k token limit vs 30k for gpt-4o

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Extract credit report data using AI
     */
    public function extractFromText($textContent)
    {
        try {
            // Clean text first
            $cleanedText = $this->cleanText($textContent);
            
            // Split into chunks to process separately
            $chunkSize = 100000; // 100k characters per chunk
            // Smart chunking: Split by logical sections instead of arbitrary length
            $chunkSize = 60000; // 60k chars is safer for tokens
            $chunks = $this->smartSplitText($cleanedText, $chunkSize);
            
            $allAccounts = [];
            $allInquiries = [];
            $allPublicRecords = [];
            $allScores = [];
            $personalInfo = [];
            $summary = [];
            
            Log::info("Processing credit report in " . count($chunks) . " chunks. Total text length: " . strlen($cleanedText));
            Log::debug("Cleaned text sample: " . substr($cleanedText, 0, 500));
            
            $successChunks = 0;
            $lastError = 'Unknown extraction error';
            foreach ($chunks as $index => $chunk) {
                Log::info("Processing chunk " . ($index + 1) . " of " . count($chunks) . " (Size: " . strlen($chunk) . " bytes)");
                
                $result = $this->extractFromChunk($chunk, $index);
                
                if ($result['success'] && !empty($result['data'])) {
                    $successChunks++;
                    $data = $result['data'];
                    $accountsInChunk = count($data['accounts'] ?? []);
                    Log::info("Chunk " . ($index + 1) . " result: $accountsInChunk accounts found.");
                    
                    // Merge personal_info (take first non-empty one)
                    if (empty($personalInfo) && !empty($data['personal_info'])) {
                        $personalInfo = $data['personal_info'];
                    }
                    
                    // Merge summary (take first non-empty one)
                    if (empty($summary) && !empty($data['summary'])) {
                        $summary = $data['summary'];
                    }
                    
                    // Merge scores (deduplicate by bureau)
                    if (!empty($data['credit_scores']) && is_array($data['credit_scores'])) {
                        foreach ($data['credit_scores'] as $score) {
                            $bureau = strtoupper($score['bureau'] ?? 'UNKNOWN');
                            $allScores[$bureau] = $score;
                        }
                    }
                    
                    // Merge accounts (avoid duplicates by creditor + last 4 digits)
                    if (!empty($data['accounts']) && is_array($data['accounts'])) {
                        foreach ($data['accounts'] as $account) {
                            $creditorName = strtolower($account['creditor_name'] ?? 'unknown');
                            $accountNumber = $account['account_number'] ?? 'unknown';
                            $last4 = substr($accountNumber, -4);
                            $key = $creditorName . '_' . $last4;

                            if (isset($allAccounts[$key])) {
                                $existing = $allAccounts[$key];
                                $new = $account;
                                
                                // Keep the one with more fields populated
                                $existingFields = count(array_filter($existing));
                                $newFields = count(array_filter($new));
                                
                                if ($newFields > $existingFields) {
                                    $allAccounts[$key] = $new;
                                }
                            } else {
                                $allAccounts[$key] = $account;
                            }
                        }
                    }
                    
                    // Merge inquiries (will deduplicate later)
                    if (!empty($data['inquiries']) && is_array($data['inquiries'])) {
                        foreach ($data['inquiries'] as $inquiry) {
                            $allInquiries[] = $inquiry;
                        }
                    }

                    // Merge public records
                    if (!empty($data['public_records']) && is_array($data['public_records'])) {
                        foreach ($data['public_records'] as $record) {
                            $allPublicRecords[] = $record;
                        }
                    }
                } else {
                    $lastError = $result['error'] ?? 'Chunk extraction failed';
                    Log::warning("Chunk " . ($index + 1) . " failed or returned no data. Error: " . $lastError);
                }
            }

            if ($successChunks === 0 && count($chunks) > 0) {
                return [
                    'success' => false,
                    'error' => 'AI Extraction failed: ' . $lastError
                ];
            }
            
            // Validate and clean accounts before returning
            Log::info("Validating " . count($allAccounts) . " total unique accounts found across all chunks.");
            $cleanedAccounts = $this->validateAndCleanAccounts(array_values($allAccounts), $cleanedText);
            
            // Deduplicate inquiries by creditor + date (±1 day tolerance)
            $uniqueInquiries = $this->deduplicateInquiries($allInquiries);
            
            Log::info("Extraction complete: " . count($cleanedAccounts) . " accounts (after validation), " . count($uniqueInquiries) . " unique inquiries, " . count($allScores) . " scores.");
            
            // Use summary from AI if available, otherwise calculate
            if (empty($summary)) {
                $summary = [
                    'total_accounts' => count($cleanedAccounts),
                    'open_accounts' => 0,
                    'derogatory_accounts' => 0,
                    'hard_inquiries_2yr' => count($uniqueInquiries)
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'personal_info' => $personalInfo,
                    'credit_scores' => array_values($allScores),
                    'accounts' => $cleanedAccounts,
                    'inquiries' => $uniqueInquiries,
                    'public_records' => $allPublicRecords,
                    'summary' => $summary
                ]
            ];

        } catch (\Exception $e) {
            Log::error('AI Extraction Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract data from a single chunk
     */
    protected function extractFromChunk($chunk, $chunkIndex)
    {
        $prompt = $this->buildExtractionPrompt();
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt
                ],
                [
                    'role' => 'user',
                    'content' => "CRITICAL INSTRUCTIONS - READ CAREFULLY:

1. FIRST: Find and extract the SUMMARY section. Look for:
   - 'Total Accounts: XX' for TransUnion, Experian, Equifax (usually 30-36 accounts)
   - 'Open Accounts: XX' (usually 15-20)
   - 'Derogatory Accounts: XX' or 'Negative Items: XX'
   - 'Hard Inquiries: XX' (last 2 years)

2. SECOND: Extract EVERY SINGLE ACCOUNT from the 'Account History' section.
   - The report typically has 30-40 accounts total
   - DO NOT STOP after finding a few accounts
   - Extract ALL accounts including:
     * Open accounts (Mortgage, Credit Cards, Auto Loans, Personal Loans)
     * Closed/Paid accounts (old loans, closed cards)
     * Negative accounts (Charge-offs, Collections)
   
3. ACCOUNT EXTRACTION RULES:
   - Each account starts with a creditor name (e.g., 'JPMCB HOME', 'CITI', 'CAPITAL ONE')
   - Extract the account number (usually last 4 digits with asterisks)
   - Get the account type, status, balance, payment status
   - For negative accounts: set is_negative: true and provide dispute_reason

4. VERIFICATION:
   - Count how many accounts you extracted
   - Compare to the Total Accounts in the Summary section
   - If you extracted less than 20 accounts, YOU MISSED SOME - go back and find them all!

Now extract from this IdentityIQ report (chunk " . ($chunkIndex + 1) . "):

" . $chunk
                ]
            ],
            'temperature' => 0.3,  // Slightly higher to be more thorough
            'response_format' => ['type' => 'json_object']
        ]);

        if ($response->successful()) {
            $content = $response->json('choices.0.message.content');
            Log::debug("Raw AI Response for chunk $chunkIndex: " . substr($content, 0, 1000) . "...");
            
            $extractedData = json_decode($this->cleanJsonResponse($content), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON decoding error for chunk $chunkIndex: " . json_last_error_msg() . " Raw content: " . $content);
                return [
                    'success' => false,
                    'error' => 'Failed to decode JSON from AI response.'
                ];
            }

            // Normalize data to ensure standard keys
            $extractedData = $this->normalizeAIExtractedData($extractedData);
            
            // Log what we extracted
            Log::info("Chunk $chunkIndex extracted: " . 
                count($extractedData['accounts'] ?? []) . " accounts, " .
                count($extractedData['inquiries'] ?? []) . " inquiries, " .
                count($extractedData['credit_scores'] ?? []) . " scores");
            
            if (!empty($extractedData['summary'])) {
                Log::info("Chunk $chunkIndex summary data: " . json_encode($extractedData['summary']));
            } else {
                Log::warning("Chunk $chunkIndex: No summary data extracted!");
            }

            return [
                'success' => true,
                'data' => $extractedData
            ];
        }

        $errorMsg = 'Chunk extraction failed';
        $responseBody = $response->json();
        if (isset($responseBody['error']['message'])) {
            $errorMsg = $responseBody['error']['message'];
        }

        Log::warning("Chunk $chunkIndex extraction failed: " . $response->body());
        return [
            'success' => false,
            'error' => $errorMsg
        ];
    }


    protected function buildExtractionPrompt()
    {
        return <<<PROMPT
You are an expert Credit Analyst. Your job is to extract data from raw text extracted from a PDF credit report (IdentityIQ).

## TEXT STRUCTURE & LAYOUT:
- The text is often formatted as **key-value pairs** on separate lines (e.g., "Account Name: CHASE").
- **Look for patterns**: "Account #", "Date Opened", "Balance" usually appear near each other for a single account.
- **CRITICAL**: IdentityIQ reports have a SUMMARY section at the beginning with pre-calculated totals. USE THESE FIRST.

## CRITICAL RULES:
1. **NO HALLUCINATION**: Only extract data that is CLEARLY visible in the text.
2. **EXACT DATA ONLY**: Copy account numbers, balances, and names EXACTLY as shown.
3. **EXTRACT ALL ACCOUNTS**: Include ALL tradelines - open, closed, paid, AND negative accounts.
4. **PARSE SUMMARY FIRST**: Look for the Summary section which contains:
   - Total Accounts (for each bureau: TransUnion, Experian, Equifax)
   - Open Accounts
   - Derogatory/Negative Accounts
   - Hard Inquiries (last 2 years)

## ACCOUNT CATEGORIZATION:
- **Negative Account**: Has status like "Charge-off", "Collection", "Delinquent", "Late Payment", "Profit & Loss Write-off"
- **Open Account**: Has status "Open" OR payment status "Current"
- **Closed Account**: Has status "Closed" or "Paid"

## RESPONSE FORMAT (JSON):
Return exactly this JSON structure. DO NOT use other keys.
{
  "summary": {
    "total_accounts_transunion": 33,
    "total_accounts_experian": 36,
    "total_accounts_equifax": 32,
    "open_accounts": 17,
    "derogatory_accounts": 2,
    "hard_inquiries_2yr": 3
  },
  "personal_info": {
    "name": "full name",
    "address": "current address"
  },
  "credit_scores": [
    { "bureau": "TransUnion", "score": 700 },
    { "bureau": "Experian", "score": 690 },
    { "bureau": "Equifax", "score": 710 }
  ],
  "accounts": [
    {
      "creditor_name": "EXACT NAME",
      "account_number": "last 4 or more digits",
      "account_type": "Revolving/Installment/Mortgage/etc",
      "account_status": "Open/Closed/Charge-off/Collection/etc",
      "payment_status": "Current/Late/etc",
      "date_opened": "YYYY-MM-DD",
      "current_balance": 123.45,
      "credit_limit": 1000.00,
      "amount_past_due": 0.00,
      "bureau": "TransUnion/Experian/Equifax (can be multiple separated by comma)",
      "is_negative": true,
      "dispute_reason": "Specific professional reason for dispute (ONLY if is_negative is true)"
    }
  ],
  "inquiries": [
    {
      "creditor_name": "NAME",
      "inquiry_date": "YYYY-MM-DD",
      "bureau": "Bureau name"
    }
  ],
  "public_records": []
}

**VERIFICATION CHECKLIST**:
- [ ] Summary section totals extracted from report (not calculated)
- [ ] Every account name matches EXACTLY what's in the text
- [ ] Every account number is from the text
- [ ] ALL accounts included (open, closed, negative) - not just negative ones
- [ ] Negative accounts have is_negative: true and dispute_reason
- [ ] Open accounts have account_status: "Open" OR payment_status: "Current"
PROMPT;
    }


    /**
     * Normalize AI extracted data to handle minor key variations
     */
    protected function normalizeAIExtractedData($data)
    {
        $normalized = [
            'personal_info' => $data['personal_info'] ?? [],
            'credit_scores' => [],
            'accounts' => [],
            'inquiries' => $data['inquiries'] ?? [],
            'public_records' => $data['public_records'] ?? [],
            'summary' => $data['summary'] ?? [],
        ];

        // 1. Normalize Scores
        if (isset($data['credit_scores'])) {
            if (is_array($data['credit_scores']) && isset($data['credit_scores'][0])) {
                $normalized['credit_scores'] = $data['credit_scores'];
            } else if (is_array($data['credit_scores'])) {
                // Handle object format: {"TransUnion": 415, ...}
                foreach ($data['credit_scores'] as $bureau => $score) {
                    if (is_numeric($score)) {
                        $normalized['credit_scores'][] = [
                            'bureau' => $bureau,
                            'score' => (int)$score
                        ];
                    } else if (is_array($score) && isset($score['score'])) {
                        $normalized['credit_scores'][] = [
                            'bureau' => $score['bureau'] ?? $bureau,
                            'score' => (int)$score['score']
                        ];
                    }
                }
            }
        }

        // 2. Normalize Accounts
        $rawAccounts = $data['accounts'] ?? $data['negative_accounts'] ?? $data['negative_items'] ?? [];
        if (is_array($rawAccounts)) {
            foreach ($rawAccounts as $rawAcc) {
                if (!is_array($rawAcc)) continue;
                
                $normalizedAcc = [
                    'creditor_name' => $rawAcc['creditor_name'] ?? $rawAcc['Creditor Name'] ?? $rawAcc['account_name'] ?? 'Unknown',
                    'account_number' => $rawAcc['account_number'] ?? $rawAcc['Account Number'] ?? $rawAcc['Account #'] ?? '',
                    'account_type' => $rawAcc['account_type'] ?? $rawAcc['Account Type'] ?? 'Unknown',
                    'account_status' => $rawAcc['account_status'] ?? $rawAcc['Account Status'] ?? $rawAcc['Status'] ?? 'Unknown',
                    'payment_status' => $rawAcc['payment_status'] ?? $rawAcc['Payment Status'] ?? '',
                    'current_balance' => $rawAcc['current_balance'] ?? $rawAcc['Current Balance'] ?? $rawAcc['Balance'] ?? 0,
                    'credit_limit' => $rawAcc['credit_limit'] ?? $rawAcc['Credit Limit'] ?? $rawAcc['High Credit'] ?? 0,
                    'amount_past_due' => $rawAcc['amount_past_due'] ?? $rawAcc['Amount Past Due'] ?? 0,
                    'bureau' => $rawAcc['bureau'] ?? $rawAcc['Bureau'] ?? '',
                    'is_negative' => $rawAcc['is_negative'] ?? false,
                    'dispute_reason' => $rawAcc['dispute_reason'] ?? $rawAcc['Dispute Reason'] ?? $rawAcc['remarks'] ?? '',
                ];
                $normalized['accounts'][] = $normalizedAcc;
            }
        }

        return $normalized;
    }

    /**
     * Validate extracted data structure
     */
    public function validateExtractedData($data)
    {
        return isset($data['accounts']) && isset($data['credit_scores']);
    }

    /**
     * Validate and clean extracted accounts to prevent hallucination
     */
    protected function validateAndCleanAccounts($accounts, $text)
    {
        $cleaned = [];
        $seen = [];
        
        foreach ($accounts as $account) {
            // Skip if missing required fields
            if (empty($account['creditor_name']) || empty($account['account_number'])) {
                Log::warning("Account missing required fields, skipping");
                continue;
            }
            
            $accountKey = strtolower($account['creditor_name']) . '_' . substr($account['account_number'], -4);
            
            // Skip if we've already seen this account
            if (isset($seen[$accountKey])) {
                Log::warning("Duplicate account detected and skipped: " . $accountKey);
                continue;
            }
            
            // Verify account exists in Text (prevent hallucination)
            if (!$this->verifyAccountInText($account, $text)) {
                Log::warning("Account not found in Text - possible hallucination or formatting issue, skipping: " . ($account['creditor_name'] ?? 'Unknown'));
                continue;
            }
            
            $seen[$accountKey] = true;
            $cleaned[] = $account;
        }
        
        return $cleaned;
    }

    /**
     * Verify that an account actually exists in the text
     */
    protected function verifyAccountInText($account, $text)
    {
        $creditorName = $account['creditor_name'] ?? '';
        $accountNumber = $account['account_number'] ?? '';
        
        if (empty($creditorName)) return false;

        // Normalize text for comparison (handle potential PDF ligatures and extra spacing)
        $normalizedText = str_replace(['ﬁ', 'ﬂ', 'ﬀ'], ['fi', 'fl', 'ff'], $text);
        $normalizedCreditor = str_replace(['ﬁ', 'ﬂ', 'ﬀ'], ['fi', 'fl', 'ff'], $creditorName);

        // Check if creditor name appears in text (case-insensitive)
        if (stripos($normalizedText, $normalizedCreditor) === false) {
            // Try partial match (first word of creditor name)
            $firstWord = explode(' ', $normalizedCreditor)[0];
            if (strlen($firstWord) > 3 && stripos($normalizedText, $firstWord) === false) {
                Log::warning("Verification failed: Creditor '$normalizedCreditor' (and first word '$firstWord') not found in text.");
                return false;
            }
        }
        
        // Check if account number appears (last 4 digits)
        if (!empty($accountNumber)) {
            $last4 = substr($accountNumber, -4);
            if (strlen($last4) === 4 && stripos($normalizedText, $last4) === false) {
                Log::warning("Account number '$last4' not found in text for creditor '$normalizedCreditor'. Proceeding with caution.");
                // Don't fail on this alone, as formatting might differ (e.g. XXXXXX1234 vs 1234)
            }
        }
        
        return true;
    }

    /**
     * Deduplicate inquiries by creditor + date (±1 day tolerance)
     * Same inquiry appearing on multiple bureaus should count as one inquiry event
     */
    protected function deduplicateInquiries($inquiries)
    {
        $uniqueInquiries = [];
        $seen = [];
        
        foreach ($inquiries as $inquiry) {
            $creditorName = strtolower(trim($inquiry['creditor_name'] ?? ''));
            $inquiryDate = $inquiry['inquiry_date'] ?? '';
            
            if (empty($creditorName) || empty($inquiryDate)) {
                continue;
            }
            
            // Convert date to timestamp for comparison
            try {
                $timestamp = strtotime($inquiryDate);
                if ($timestamp === false) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }
            
            // Check if we've seen a similar inquiry (same creditor within ±1 day)
            $isDuplicate = false;
            foreach ($seen as $seenKey => $seenTimestamp) {
                list($seenCreditor, $seenDate) = explode('|', $seenKey);
                
                // Same creditor and within 1 day
                if ($seenCreditor === $creditorName && abs($timestamp - $seenTimestamp) <= 86400) {
                    $isDuplicate = true;
                    break;
                }
            }
            
            if (!$isDuplicate) {
                $key = $creditorName . '|' . $inquiryDate;
                $seen[$key] = $timestamp;
                $uniqueInquiries[] = $inquiry;
            }
        }
        
        return $uniqueInquiries;
    }


    /**
     * Smart split text to avoid breaking accounts in the middle
     */
    protected function smartSplitText($text, $maxSize)
    {
        $chunks = [];
        $length = strlen($text);
        $offset = 0;
        
        while ($offset < $length) {
            // If remaining text is smaller than max size, just take it
            if (($length - $offset) <= $maxSize) {
                $chunks[] = substr($text, $offset);
                break;
            }
            
            // Look for a good break point within the last 20% of the chunk
            $searchStart = $offset + ($maxSize * 0.8);
            $searchLength = $maxSize * 0.2;
            $searchArea = substr($text, $searchStart, $searchLength);
            
            // Try to break at "Account Name" or double newline
            $breakPos = false;
            
            // Priority 1: "Account Name" (start of new account)
            $keywords = ['Account Name', 'Creditor Name', 'Company Name'];
            foreach ($keywords as $keyword) {
                $pos = stripos($searchArea, $keyword);
                if ($pos !== false) {
                    $breakPos = $searchStart + $pos;
                    break;
                }
            }
            
            // Priority 2: Double newline (likely section break)
            if ($breakPos === false) {
                $pos = strrpos($searchArea, "\n\n");
                if ($pos !== false) {
                    $breakPos = $searchStart + $pos;
                }
            }
            
            // Priority 3: Single newline (last resort)
            if ($breakPos === false) {
                $pos = strrpos($searchArea, "\n");
                if ($pos !== false) {
                    $breakPos = $searchStart + $pos;
                }
            }
            
            // Fallback: Hard break if no good point found
            if ($breakPos === false) {
                $breakPos = $offset + $maxSize;
            }
            
            $chunks[] = substr($text, $offset, $breakPos - $offset);
            $offset = $breakPos;
        }
        
        return $chunks;
    }

    /**
     * Clean Text to reduce token count while preserving credit data
     */
    protected function cleanText($text)
    {
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove valid-looking whitespace but keep double newlines for structure
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s+/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text); // Max 2 newlines
        
        return trim($text);
    }

    /**
     * Clean JSON response from AI (remove markdown code blocks)
     */
    protected function cleanJsonResponse($json)
    {
        // Remove markdown code block markers if present
        $json = preg_replace('/^```json\s*/i', '', $json);
        $json = preg_replace('/^```\s*/i', '', $json);
        $json = preg_replace('/\s*```$/', '', $json);
        
        return trim($json);
    }
}
