<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IdentityIQParserService
{
    protected $html;
    protected $dom;
    protected $xpath;

    public function __construct($htmlContent)
    {
        $this->html = $htmlContent;
        $this->initializeDom();
    }

    protected function initializeDom()
    {
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);
        // Ensure UTF-8 loading
        $this->dom->loadHTML('<?xml encoding="UTF-8">' . $this->html);
        libxml_clear_errors();
        $this->xpath = new DOMXPath($this->dom);
    }

    /**
     * Parse personal information from the report
     */
    public function parsePersonalInfo()
    {
        $personalInfo = [];

        // Extract name
        $nameNodes = $this->xpath->query("//ng-include[contains(@src, 'personNameTemplate')]//div[contains(@class, 'ng-binding')] | //td[contains(@class, 'info')]//div[contains(@class, 'ng-binding')]");
        if ($nameNodes->length > 0) {
            $names = [];
            foreach ($nameNodes as $node) {
                $text = $this->cleanText($node->textContent);
                if (!empty($text)) {
                    $names[] = $text;
                }
            }
            if (!empty($names)) {
                $personalInfo['first_name'] = $names[0] ?? '';
                $personalInfo['last_name'] = end($names) ?? '';
                $personalInfo['middle_name'] = (count($names) > 2) ? implode(' ', array_slice($names, 1, -1)) : '';
            }
        }

        // Extract date of birth
        $dobNodes = $this->xpath->query("//td[contains(text(), 'Date of Birth:')]/following-sibling::td//div");
        if ($dobNodes->length > 0) {
            $personalInfo['date_of_birth'] = $this->cleanText($dobNodes->item(0)->textContent);
        }

        // Extract current address
        $addressNodes = $this->xpath->query("//td[contains(text(), 'Current Address(es):')]/following-sibling::td//ng-include | //td[contains(text(), 'Current Address')]/following-sibling::td");
        if ($addressNodes->length > 0) {
            $personalInfo['current_address'] = $this->cleanText($addressNodes->item(0)->textContent);
        }

        // Parse Name variations, address variations, and wrong employers (to set identifiers answers)
        $identifiers = [];
        
        // Count name variations
        $nameVarNodes = $this->xpath->query("//table[contains(@class, 'name-variations')]//tr | //td[contains(text(), 'Name Variations:')]/following-sibling::td//div");
        if ($nameVarNodes->length > 1) {
            $identifiers[] = 'namevar';
        }

        // Count address variations
        $addrVarNodes = $this->xpath->query("//table[contains(@class, 'address-variations')]//tr | //td[contains(text(), 'Previous Address')]/following-sibling::td");
        if ($addrVarNodes->length > 1) {
            $identifiers[] = 'oldaddr';
        }

        // Count employers / wrong info
        $empNodes = $this->xpath->query("//table[contains(@class, 'employer')]//tr | //td[contains(text(), 'Employer')]/following-sibling::td");
        if ($empNodes->length > 1) {
            $identifiers[] = 'otherid';
        }

        $personalInfo['identifiers'] = empty($identifiers) ? ['none'] : $identifiers;

        return $personalInfo;
    }

    /**
     * Parse credit scores from all three bureaus
     */
    public function parseCreditScores()
    {
        $scores = [];
        
        // TransUnion Score
        $tucScoreNode = $this->xpath->query("//th[contains(@class, 'headerTUC') and contains(text(), 'TransUnion')]/ancestor::tr/following-sibling::tr//td[contains(text(), 'Credit Score:')]/following-sibling::td[@class='info']");
        if ($tucScoreNode->length > 0) {
            $scoreText = $this->cleanText($tucScoreNode->item(0)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'TransUnion',
                    'score' => (int)$scoreText,
                ];
            }
        }

        // Experian Score
        $expScoreNodes = $this->xpath->query("//th[contains(@class, 'headerEXP') and contains(text(), 'Experian')]/ancestor::tr/following-sibling::tr//td[contains(text(), 'Credit Score:')]/following-sibling::td[@class='info']");
        if ($expScoreNodes->length > 0) {
            $scoreText = $this->cleanText($expScoreNodes->item(0)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'Experian',
                    'score' => (int)$scoreText,
                ];
            }
        }

        // Equifax Score
        $eqfScoreNodes = $this->xpath->query("//th[contains(@class, 'headerEQF') and contains(text(), 'Equifax')]/ancestor::tr/following-sibling::tr//td[contains(text(), 'Credit Score:')]/following-sibling::td[@class='info']");
        if ($eqfScoreNodes->length > 0) {
            $scoreText = $this->cleanText($eqfScoreNodes->item(0)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'Equifax',
                    'score' => (int)$scoreText,
                ];
            }
        }

        return $scores;
    }

    /**
     * Parse credit accounts/tradelines
     */
    public function parseCreditAccounts()
    {
        $accounts = [];
        
        // Find subheaders
        $subHeaders = $this->xpath->query("//div[contains(@class, 'sub_header')]");
        
        foreach ($subHeaders as $header) {
            $creditorName = $this->cleanText($header->textContent);
            // Remove "Original Creditor:" tags
            $creditorName = trim(preg_replace('/\(Original Creditor:.*?\)/i', '', $creditorName));
            
            if (empty($creditorName) || str_contains(strtolower($creditorName), 'risk factors') || str_contains(strtolower($creditorName), 'summary')) {
                continue;
            }

            // The table is usually the next sibling
            $tableNode = $this->xpath->query("./following-sibling::table[1]", $header)->item(0);
            if (!$tableNode) {
                continue;
            }

            $getField = function($label) use ($tableNode) {
                $cells = [];
                $nodes = $this->xpath->query(".//td[contains(text(), '$label')]/following-sibling::td", $tableNode);
                foreach ($nodes as $node) {
                    $cells[] = $this->cleanText($node->textContent);
                }
                return $cells;
            };

            $accountType = $getField('Account Type:')[0] ?? '';
            $accountTypeDetail = $getField('Account Type - Detail:')[0] ?? '';
            $accountStatus = $getField('Account Status:')[0] ?? '';
            $paymentStatus = $getField('Payment Status:')[0] ?? '';
            $balanceStr = $getField('Balance:')[0] ?? '$0';
            $pastDueStr = $getField('Past Due:')[0] ?? '$0';
            $dateOpened = $getField('Date Opened:')[0] ?? '';
            $lastReported = $getField('Last Reported:')[0] ?? '';
            $commentsList = $getField('Comments:');
            $comments = implode(' ', $commentsList);

            // Parse numeric values
            $balance = $this->parseDollar($balanceStr);
            $pastDue = $this->parseDollar($pastDueStr);

            // Identify type flags
            $statusLower = strtolower($accountStatus);
            $payStatusLower = strtolower($paymentStatus);
            $detailLower = strtolower($accountTypeDetail);
            $commentsLower = strtolower($comments);

            $isChargeoff = str_contains($statusLower, 'charge') || str_contains($commentsLower, 'charged off') || 
                           str_contains($commentsLower, 'profit and loss') || str_contains($commentsLower, 'bad debt') ||
                           str_contains($detailLower, 'chargeoff') || str_contains($payStatusLower, 'chargeoff') ||
                           str_contains($payStatusLower, 'charge-off');

            $isCollection = str_contains($detailLower, 'collection') || str_contains($statusLower, 'collection') ||
                            str_contains($payStatusLower, 'collection');

            $isRepo = str_contains($statusLower, 'repossess') || str_contains($commentsLower, 'repossess') ||
                      str_contains($detailLower, 'repossess');

            // Count late marks inside history grids if available
            $lateMarks = 0;
            $parentWrapper = $this->xpath->query("./ancestor::div[contains(@class, 'rpt_content_wrapper') or contains(@class, 'ng-scope')]", $header)->item(0);
            if ($parentWrapper) {
                $histCells = $this->xpath->query(".//td[contains(@class, 'hstry-30') or contains(@class, 'hstry-60') or contains(@class, 'hstry-90') or contains(@class, 'hstry-120') or contains(@class, 'hstry-co')]", $parentWrapper);
                $lateMarks = $histCells->length;
            }

            $hasLates = $lateMarks > 0 || str_contains($payStatusLower, 'late') || str_contains($payStatusLower, 'delinquent');

            // Determine chargeoff status
            $coStatus = 'unsure';
            if ($isChargeoff) {
                $sixMonthsAgo = Carbon::now()->subMonths(6);
                $openedDate = $this->parseDate($dateOpened);
                $reportedDate = $this->parseDate($lastReported);
                $oneMonthAgo = Carbon::now()->subMonth();

                if (str_contains($commentsLower, 'charged off as bad debt') || str_contains($commentsLower, 'profit and loss')) {
                    $coStatus = 'remarked';
                } elseif ($openedDate && $openedDate->greaterThan($sixMonthsAgo)) {
                    $coStatus = 'under6';
                } elseif ($reportedDate && $reportedDate->lessThan($oneMonthAgo)) {
                    $coStatus = 'stopped';
                } elseif ($reportedDate && $reportedDate->greaterThanOrEqualTo($oneMonthAgo)) {
                    $coStatus = 'activeold';
                }
            }

            $accounts[] = [
                'creditor_name' => $creditorName,
                'account_number' => $getField('Account Number:')[0] ?? 'N/A',
                'account_type' => $accountType,
                'account_status' => $accountStatus,
                'payment_status' => $paymentStatus,
                'balance' => $balance,
                'past_due' => $pastDue,
                'is_chargeoff' => $isChargeoff,
                'is_collection' => $isCollection,
                'is_repo' => $isRepo,
                'has_lates' => $hasLates,
                'late_marks' => $lateMarks,
                'co_status' => $coStatus,
                'bureau' => $this->determineAccountBureaus($tableNode),
                'comments' => $comments,
            ];
        }

        return $accounts;
    }

    /**
     * Determine reporting bureaus for an account table
     */
    protected function determineAccountBureaus($tableNode): string
    {
        $bureaus = [];
        $headerRow = $this->xpath->query(".//tr[1]/th", $tableNode);
        if ($headerRow->length > 0) {
            foreach ($headerRow as $th) {
                $txt = strtolower($th->textContent);
                if (str_contains($txt, 'equifax')) $bureaus[] = 'Equifax';
                if (str_contains($txt, 'experian')) $bureaus[] = 'Experian';
                if (str_contains($txt, 'transunion')) $bureaus[] = 'TransUnion';
            }
        }
        return empty($bureaus) ? 'All' : implode(', ', $bureaus);
    }

    /**
     * Parse credit inquiries
     */
    public function parseInquiries()
    {
        $inquiries = [];
        $inqRows = $this->xpath->query("//table[contains(@class, 'inquiry')]//tr[position() > 1] | //td[contains(text(), 'Inquiries:')]/ancestor::table//tr[position() > 1]");
        
        foreach ($inqRows as $row) {
            $cols = $this->xpath->query(".//td", $row);
            if ($cols->length >= 3) {
                $inquiries[] = [
                    'creditor_name' => $this->cleanText($cols->item(0)->textContent),
                    'inquiry_date' => $this->cleanText($cols->item(1)->textContent),
                    'bureau' => $this->cleanText($cols->item(2)->textContent),
                ];
            }
        }
        
        return $inquiries;
    }

    /**
     * Parse public records
     */
    public function parsePublicRecords()
    {
        $publicRecords = [];
        // Extract bankruptcies or judgments if visible
        $prRows = $this->xpath->query("//table[contains(@class, 'public-record')]//tr | //div[contains(@class, 'public-record')]");
        foreach ($prRows as $row) {
            $publicRecords[] = [
                'type' => $this->cleanText($row->textContent),
            ];
        }
        return $publicRecords;
    }

    /**
     * Extract report reference and metadata
     */
    public function parseReportMetadata()
    {
        $metadata = [];
        
        $refNodes = $this->xpath->query("//td[contains(text(), 'Reference #:')]/following-sibling::td");
        if ($refNodes->length > 0) {
            $metadata['reference_number'] = $this->cleanText($refNodes->item(0)->textContent);
        }

        $dateNodes = $this->xpath->query("//td[contains(text(), 'Report Date:')]/following-sibling::td");
        if ($dateNodes->length > 0) {
            $metadata['report_date'] = $this->cleanText($dateNodes->item(0)->textContent);
        }

        return $metadata;
    }

    /**
     * Clean text by removing extra whitespace
     */
    protected function cleanText($text)
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        return trim($text);
    }

    /**
     * Parse a date string
     */
    protected function parseDate($dateString)
    {
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clean dollar values to float
     */
    protected function parseDollar($val): float
    {
        $clean = preg_replace('/[^\d\.]/', '', $val);
        return (float)($clean ?: 0.0);
    }

    /**
     * Parse all data into a unified array structure
     */
    public function parseAll()
    {
        $personal = $this->parsePersonalInfo();
        $scores = $this->parseCreditScores();
        $accounts = $this->parseCreditAccounts();
        $inquiries = $this->parseInquiries();
        $public = $this->parsePublicRecords();
        $metadata = $this->parseReportMetadata();

        // Calculate summary counts
        $derogatoryCount = 0;
        foreach ($accounts as $acc) {
            if ($acc['is_collection'] || $acc['is_chargeoff'] || $acc['is_repo']) {
                $derogatoryCount++;
            }
        }

        return [
            'metadata' => $metadata,
            'personal_info' => $personal,
            'credit_scores' => $scores,
            'accounts' => $accounts,
            'inquiries' => $inquiries,
            'public_records' => $public,
            'summary' => [
                'total_accounts' => count($accounts),
                'open_accounts' => count(array_filter($accounts, fn($a) => str_contains(strtolower($a['account_status']), 'open'))),
                'derogatory_accounts' => $derogatoryCount,
                'hard_inquiries_2yr' => count($inquiries),
            ]
        ];
    }
}
