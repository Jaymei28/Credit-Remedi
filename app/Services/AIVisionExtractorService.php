<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class AIVisionExtractorService
{
    protected $apiKey;
    protected $model = 'gpt-4o-mini'; // Uses vision and is extremely cost-effective

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Extract credit report details from an uploaded image (screenshot)
     */
    public function extractFromImage(UploadedFile $file): array
    {
        try {
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();

            $prompt = $this->buildVisionPrompt();

            Log::info("Sending screenshot upload to OpenAI Vision API ({$this->model})");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:' . $mimeType . ';base64,' . $base64
                                ]
                            ]
                        ]
                    ]
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1
            ]);

            if (!$response->ok()) {
                throw new \Exception("OpenAI API returned error: " . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new \Exception("Empty response content from OpenAI Vision API");
            }

            $parsedData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to decode JSON response from AI: " . json_last_error_msg());
            }

            return [
                'success' => true,
                'data' => $parsedData
            ];

        } catch (\Exception $e) {
            Log::error("Vision Extraction Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the structured vision prompts mapping report items
     */
    protected function buildVisionPrompt(): string
    {
        return <<<EOT
You are an expert Credit Report extraction assistant. Your job is to analyze the uploaded image (which should be a screenshot of a credit report, credit karma dashboard, or similar credit monitoring tool) and return structured data in JSON.

CRITICAL FIRST STEP (Verification):
Identify if the image is actually a credit report, credit bureau page, credit dashboard (like Credit Karma, Smart Credit, IdentityIQ, Experian, MyFICO), or a list of credit accounts/scores.
If the image is completely unrelated (e.g. a pet, a car, food, random scenery, general documents like receipts or utility bills that are NOT credit reports), set "is_valid_report": false in the JSON and leave all other fields empty.

If it IS a valid credit report screenshot, set "is_valid_report": true and extract the following:
1. "score": Look for a credit score (e.g., 550, 620, 780). Map it to one of these categories: "sub580" (if < 580), "580_669", "670_739", "740plus", or "unknown" (if not visible).
2. "personal_info": Look for personal information details. Include fields: "first_name", "last_name", "date_of_birth" (if shown), "current_address" (if shown).
3. "accounts": Extract every credit account visible in the image. For each account:
   - "creditor_name": Name of the bank, lender, or collection agency (e.g., CITI, MIDLAND FUNDING, SELFRENT).
   - "account_number": Full or partial account number (e.g., XXXXXX1234).
   - "account_status": Set to "negative" if it is in collection, charge-off, repossession, or delinquent. Otherwise set to "positive" or "neutral".
   - "balance": Balance due (numeric value, e.g. 1200 or 0).
   - "past_due": Past due amount if shown (numeric).
   - "bureau": The reporting credit bureau(s) if shown (Equifax, Experian, TransUnion, or a comma-separated list of multiple).
   - "account_type": Type of account (e.g. revolving, collection, charge-off, late payment, repo).
4. "inquiries": Look for hard inquiries. For each:
   - "creditor_name": Name of the company pulling credit.
   - "inquiry_date": Date of inquiry.
   - "bureau": Bureau it was pulled from.
5. "summary": Look for summary metrics. If visible, extract:
   - "total_accounts": Total accounts count.
   - "open_accounts": Open accounts count.
   - "derogatory_accounts": Number of negative/derogatory accounts.
   - "hard_inquiries_2yr": Hard inquiries count.

CRITICAL: Return ONLY a valid JSON object. Do not wrap it in markdown block code fences. No preamble. Match this exact JSON layout structure:

{
  "is_valid_report": true,
  "score": "580_669",
  "personal_info": {
    "first_name": "John",
    "last_name": "Doe",
    "date_of_birth": "01/01/1980",
    "current_address": "123 Main St, Anytown, US"
  },
  "accounts": [
    {
      "creditor_name": "Midland Funding",
      "account_number": "123456XXXX",
      "account_status": "negative",
      "balance": 1250.00,
      "past_due": 1250.00,
      "bureau": "Experian, TransUnion",
      "account_type": "collection"
    }
  ],
  "inquiries": [],
  "summary": {
    "total_accounts": 12,
    "open_accounts": 8,
    "derogatory_accounts": 1,
    "hard_inquiries_2yr": 3
  }
}
EOT;
    }
}
