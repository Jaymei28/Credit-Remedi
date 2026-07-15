<?php

namespace App\Services;

use App\Models\User;
use App\Models\FundabilityScore;
use App\Models\CreditReport;
use App\Models\CreditScore;
use App\Models\CreditAccount;
use App\Models\CreditInquiry;
use App\Models\CreditPublicRecord;
use App\Models\DisputeLetter;

class FundabilityScoreService
{
    /**
     * Calculate fundability score for a user.
     */
    public function calculateScore(User $user): FundabilityScore
    {
        // Get latest Credit Report data
        $latestReport = CreditReport::where('user_id', $user->id)
            ->latest()
            ->first();

        // Get dispute statistics
        $totalDisputes = DisputeLetter::where('user_id', $user->id)->count();
        $completedDisputes = DisputeLetter::where('user_id', $user->id)
            ->where('posted_1', true)
            ->count();

        // Initialize score components
        $creditScorePoints = 0;
        $accountHealthPoints = 0;
        $inquiryPoints = 0;
        $negativeItemsPoints = 0;
        $disputeActivityPoints = 0;

        // Extract data from report
        $creditScore = 0;
        $totalAccounts = 0;
        $openAccounts = 0;
        $hardInquiries = 0;
        $negativeItems = 0;

        if ($latestReport) {
            // Get credit scores from related CreditScore model
            $scores = CreditScore::where('credit_report_id', $latestReport->id)->get();
            if ($scores->count() > 0) {
                $creditScore = $scores->avg('score');
            }

            // Get account counts from CreditAccount model
            $accounts = CreditAccount::where('credit_report_id', $latestReport->id)->get();
            $totalAccounts = $accounts->count();
            $openAccounts = $accounts->where('status', 'Open')->count();

            // Get inquiry counts from CreditInquiry model
            $inquiries = CreditInquiry::where('credit_report_id', $latestReport->id)->get();
            $hardInquiries = $inquiries->count();

            // Estimate negative items from disputes and public records
            $publicRecords = CreditPublicRecord::where('credit_report_id', $latestReport->id)->count();
            $negativeItems = $totalDisputes + $publicRecords;
        }

        // 1. Credit Score Component (40 points max)
        if ($creditScore >= 750) {
            $creditScorePoints = 40;
        } elseif ($creditScore >= 700) {
            $creditScorePoints = 35;
        } elseif ($creditScore >= 650) {
            $creditScorePoints = 28;
        } elseif ($creditScore >= 600) {
            $creditScorePoints = 20;
        } elseif ($creditScore >= 550) {
            $creditScorePoints = 12;
        } else {
            $creditScorePoints = 5;
        }

        // 2. Account Health Component (25 points max)
        if ($totalAccounts >= 10 && $openAccounts >= 3) {
            $accountHealthPoints = 25;
        } elseif ($totalAccounts >= 5 && $openAccounts >= 2) {
            $accountHealthPoints = 18;
        } elseif ($totalAccounts >= 3) {
            $accountHealthPoints = 12;
        } elseif ($totalAccounts >= 1) {
            $accountHealthPoints = 6;
        }

        // 3. Hard Inquiries Component (15 points max)
        if ($hardInquiries === 0) {
            $inquiryPoints = 15;
        } elseif ($hardInquiries <= 2) {
            $inquiryPoints = 12;
        } elseif ($hardInquiries <= 4) {
            $inquiryPoints = 8;
        } elseif ($hardInquiries <= 6) {
            $inquiryPoints = 4;
        } else {
            $inquiryPoints = 0;
        }

        // 4. Negative Items Component (15 points max)
        if ($negativeItems === 0) {
            $negativeItemsPoints = 15;
        } elseif ($negativeItems <= 2) {
            $negativeItemsPoints = 10;
        } elseif ($negativeItems <= 5) {
            $negativeItemsPoints = 6;
        } elseif ($negativeItems <= 10) {
            $negativeItemsPoints = 3;
        } else {
            $negativeItemsPoints = 0;
        }

        // 5. Dispute Activity Bonus (5 points max)
        if ($completedDisputes >= 5) {
            $disputeActivityPoints = 5;
        } elseif ($completedDisputes >= 3) {
            $disputeActivityPoints = 3;
        } elseif ($completedDisputes >= 1) {
            $disputeActivityPoints = 2;
        }

        // Calculate total score
        $totalScore = $creditScorePoints + $accountHealthPoints + $inquiryPoints + 
                     $negativeItemsPoints + $disputeActivityPoints;

        // Build factors array
        $factors = [
            'credit_score' => [
                'value' => round($creditScore),
                'points' => $creditScorePoints,
                'max_points' => 40,
                'percentage' => round(($creditScorePoints / 40) * 100),
            ],
            'account_health' => [
                'total_accounts' => $totalAccounts,
                'open_accounts' => $openAccounts,
                'points' => $accountHealthPoints,
                'max_points' => 25,
                'percentage' => round(($accountHealthPoints / 25) * 100),
            ],
            'hard_inquiries' => [
                'count' => $hardInquiries,
                'points' => $inquiryPoints,
                'max_points' => 15,
                'percentage' => round(($inquiryPoints / 15) * 100),
            ],
            'negative_items' => [
                'count' => $negativeItems,
                'points' => $negativeItemsPoints,
                'max_points' => 15,
                'percentage' => round(($negativeItemsPoints / 15) * 100),
            ],
            'dispute_activity' => [
                'completed' => $completedDisputes,
                'points' => $disputeActivityPoints,
                'max_points' => 5,
                'percentage' => round(($disputeActivityPoints / 5) * 100),
            ],
        ];

        // Build strengths and weaknesses
        $strengths = [];
        $weaknesses = [];

        if ($creditScore >= 700) {
            $strengths[] = "Excellent credit score ({$creditScore})";
        } elseif ($creditScore < 600) {
            $weaknesses[] = "Credit score needs improvement ({$creditScore})";
        }

        if ($totalAccounts >= 5) {
            $strengths[] = "Good credit history with {$totalAccounts} accounts";
        } elseif ($totalAccounts < 3) {
            $weaknesses[] = "Limited credit history";
        }

        if ($hardInquiries <= 2) {
            $strengths[] = "Few recent credit inquiries";
        } elseif ($hardInquiries > 4) {
            $weaknesses[] = "Too many recent credit inquiries ({$hardInquiries})";
        }

        if ($negativeItems === 0) {
            $strengths[] = "No negative items on report";
        } elseif ($negativeItems > 5) {
            $weaknesses[] = "Multiple negative items need attention";
        }

        if ($completedDisputes >= 3) {
            $strengths[] = "Actively working on credit repair";
        }

        // Build recommendations
        $recommendations = $this->generateRecommendations(
            $creditScore,
            $totalAccounts,
            $openAccounts,
            $hardInquiries,
            $negativeItems,
            $completedDisputes
        );

        // Create or update fundability score
        $fundabilityScore = FundabilityScore::updateOrCreate(
            ['user_id' => $user->id],
            [
                'score' => $totalScore,
                'grade' => $this->calculateGrade($totalScore),
                'factors' => $factors,
                'recommendations' => $recommendations,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'credit_score' => round($creditScore),
                'total_accounts' => $totalAccounts,
                'open_accounts' => $openAccounts,
                'hard_inquiries' => $hardInquiries,
                'negative_items' => $negativeItems,
            ]
        );

        return $fundabilityScore;
    }

    /**
     * Calculate grade based on score.
     */
    private function calculateGrade(int $score): string
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Generate personalized recommendations.
     */
    private function generateRecommendations(
        float $creditScore,
        int $totalAccounts,
        int $openAccounts,
        int $hardInquiries,
        int $negativeItems,
        int $completedDisputes
    ): array {
        $recommendations = [];

        // Credit score recommendations
        if ($creditScore < 650) {
            $recommendations[] = [
                'title' => 'Improve Your Credit Score',
                'description' => 'Focus on paying bills on time and reducing credit utilization to boost your score.',
                'priority' => 'high',
                'icon' => 'graph-up-arrow',
            ];
        }

        // Negative items recommendations
        if ($negativeItems > 0) {
            $recommendations[] = [
                'title' => 'Dispute Negative Items',
                'description' => "You have {$negativeItems} items that could be disputed. Use our AI-powered dispute tool to challenge inaccurate items.",
                'priority' => 'high',
                'icon' => 'shield-check',
                'action_url' => route('credit-repair-bot'),
                'action_text' => 'Start Dispute',
            ];
        }

        // Hard inquiries recommendations
        if ($hardInquiries > 4) {
            $recommendations[] = [
                'title' => 'Reduce Credit Inquiries',
                'description' => 'Avoid applying for new credit for the next 6 months to let inquiries age off.',
                'priority' => 'medium',
                'icon' => 'pause-circle',
            ];
        }

        // Account diversity recommendations
        if ($totalAccounts < 5) {
            $recommendations[] = [
                'title' => 'Build Credit History',
                'description' => 'Consider adding a secured credit card or becoming an authorized user to diversify your credit mix.',
                'priority' => 'medium',
                'icon' => 'credit-card',
                'action_url' => route('credit-builders.index'),
                'action_text' => 'View Credit Builders',
            ];
        }

        // Dispute activity recommendations
        if ($completedDisputes === 0 && $negativeItems > 0) {
            $recommendations[] = [
                'title' => 'Start Disputing',
                'description' => 'Begin your credit repair journey by filing your first dispute letter.',
                'priority' => 'high',
                'icon' => 'file-earmark-text',
            ];
        }

        // Payment history recommendations
        $recommendations[] = [
            'title' => 'Maintain On-Time Payments',
            'description' => 'Payment history is 35% of your credit score. Set up automatic payments to never miss a due date.',
            'priority' => 'high',
            'icon' => 'calendar-check',
        ];

        return $recommendations;
    }
}
