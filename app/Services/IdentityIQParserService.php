<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Carbon\Carbon;

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
        $this->dom->loadHTML($this->html);
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
        $nameNodes = $this->xpath->query("//ng-include[contains(@src, 'personNameTemplate')]//ng-if");
        if ($nameNodes->length > 0) {
            $firstName = '';
            $middleName = '';
            $lastName = '';
            
            foreach ($nameNodes as $node) {
                $text = trim($node->textContent);
                $text = str_replace('&nbsp;', '', $text);
                $text = trim($text);
                
                if (!empty($text)) {
                    if (empty($firstName)) {
                        $firstName = $text;
                    } elseif (empty($lastName)) {
                        $lastName = $text;
                    } else {
                        $middleName = $text;
                    }
                }
            }
            
            $personalInfo['first_name'] = $firstName;
            $personalInfo['middle_name'] = $middleName;
            $personalInfo['last_name'] = $lastName;
        }

        // Extract date of birth
        $dobNodes = $this->xpath->query("//td[contains(text(), 'Date of Birth:')]/following-sibling::td//div");
        if ($dobNodes->length > 0) {
            $personalInfo['date_of_birth'] = trim($dobNodes->item(0)->textContent);
        }

        // Extract current address
        $addressNodes = $this->xpath->query("//td[contains(text(), 'Current Address(es):')]/following-sibling::td//ng-include");
        if ($addressNodes->length > 0) {
            $addressText = trim($addressNodes->item(0)->textContent);
            $personalInfo['current_address'] = $this->cleanText($addressText);
        }

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
            $scoreText = trim($tucScoreNode->item(0)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'TransUnion',
                    'bureau_code' => 'TUC',
                    'score' => (int)$scoreText,
                    'risk_factors' => $this->parseRiskFactors('TUC'),
                ];
            }
        }

        // Experian Score
        $expScoreNodes = $this->xpath->query("//th[contains(@class, 'headerEXP') and contains(text(), 'Experian')]/ancestor::tr/following-sibling::tr//td[contains(text(), 'Credit Score:')]/following-sibling::td[@class='info']");
        if ($expScoreNodes->length > 1) {
            $scoreText = trim($expScoreNodes->item(1)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'Experian',
                    'bureau_code' => 'EXP',
                    'score' => (int)$scoreText,
                    'risk_factors' => $this->parseRiskFactors('EXP'),
                ];
            }
        }

        // Equifax Score
        $eqfScoreNodes = $this->xpath->query("//th[contains(@class, 'headerEQF') and contains(text(), 'Equifax')]/ancestor::tr/following-sibling::tr//td[contains(text(), 'Credit Score:')]/following-sibling::td[@class='info']");
        if ($eqfScoreNodes->length > 2) {
            $scoreText = trim($eqfScoreNodes->item(2)->textContent);
            if (is_numeric($scoreText)) {
                $scores[] = [
                    'bureau' => 'Equifax',
                    'bureau_code' => 'EQF',
                    'score' => (int)$scoreText,
                    'risk_factors' => $this->parseRiskFactors('EQF'),
                ];
            }
        }

        return $scores;
    }

    /**
     * Parse risk factors for a specific bureau
     */
    protected function parseRiskFactors($bureauCode)
    {
        $riskFactors = [];
        
        $factorNodes = $this->xpath->query("//td[contains(@class, strtolower('$bureauCode') . '_header') and contains(text(), 'TransUnion' or contains(text(), 'Experian') or contains(text(), 'Equifax'))]/following-sibling::td//b");
        
        foreach ($factorNodes as $node) {
            $factor = trim($node->textContent);
            if (!empty($factor)) {
                $riskFactors[] = $factor;
            }
        }
        
        return $riskFactors;
    }

    /**
     * Parse credit accounts/tradelines
     */
    public function parseCreditAccounts()
    {
        $accounts = [];
        
        // This is a simplified parser - the actual IdentityIQ HTML is complex
        // You may need to adjust based on the exact structure
        
        // Look for account information in the HTML
        $accountSections = $this->xpath->query("//div[contains(@class, 'rpt_content_wrapper')]");
        
        // Parse account details from tables
        // Note: This is a basic implementation. You'll need to enhance it based on actual HTML structure
        
        return $accounts;
    }

    /**
     * Parse credit inquiries
     */
    public function parseInquiries()
    {
        $inquiries = [];
        
        // Parse inquiry sections from the HTML
        // This would need to be customized based on the actual HTML structure
        
        return $inquiries;
    }

    /**
     * Parse public records
     */
    public function parsePublicRecords()
    {
        $publicRecords = [];
        
        // Parse public records sections from the HTML
        // This would need to be customized based on the actual HTML structure
        
        return $publicRecords;
    }

    /**
     * Parse report metadata
     */
    public function parseReportMetadata()
    {
        $metadata = [];
        
        // Extract reference number
        $refNodes = $this->xpath->query("//h3[contains(text(), 'Reference #:')]/following-sibling::p");
        if ($refNodes->length > 0) {
            $metadata['reference_number'] = trim($refNodes->item(0)->textContent);
        }

        // Extract report date
        $dateNodes = $this->xpath->query("//h3[contains(text(), 'Report Date:')]/following-sibling::p//ng");
        if ($dateNodes->length > 0) {
            $metadata['report_date'] = trim($dateNodes->item(0)->textContent);
        }

        return $metadata;
    }

    /**
     * Clean text by removing extra whitespace and special characters
     */
    protected function cleanText($text)
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        return trim($text);
    }

    /**
     * Parse a date string to Carbon instance
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
     * Parse all data from the report
     */
    public function parseAll()
    {
        return [
            'metadata' => $this->parseReportMetadata(),
            'personal_info' => $this->parsePersonalInfo(),
            'credit_scores' => $this->parseCreditScores(),
            'accounts' => $this->parseCreditAccounts(),
            'inquiries' => $this->parseInquiries(),
            'public_records' => $this->parsePublicRecords(),
        ];
    }
}
