<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lender;
use App\Models\LenderMatch;
use App\Models\FundabilityScore;

class LenderMatchingService
{
    /**
     * Find matching lenders for a user based on their fundability score.
     */
    public function findMatches(User $user, FundabilityScore $fundabilityScore): array
    {
        // Delete old matches for this user
        LenderMatch::where('user_id', $user->id)->delete();

        // Get all active lenders
        $lenders = Lender::active()->get();

        $matches = [];

        foreach ($lenders as $lender) {
            $matchScore = $this->calculateMatchScore($fundabilityScore, $lender);
            
            if ($matchScore > 0) {
                $approvalLikelihood = $this->determineApprovalLikelihood($matchScore);
                $estimatedApr = $this->estimateApr($fundabilityScore, $lender);
                $matchReasons = $this->generateMatchReasons($fundabilityScore, $lender, $matchScore);

                $match = LenderMatch::create([
                    'user_id' => $user->id,
                    'lender_id' => $lender->id,
                    'fundability_score_id' => $fundabilityScore->id,
                    'match_score' => $matchScore,
                    'approval_likelihood' => $approvalLikelihood,
                    'estimated_apr_min' => $estimatedApr['min'],
                    'estimated_apr_max' => $estimatedApr['max'],
                    'match_reasons' => $matchReasons,
                ]);

                $matches[] = $match;
            }
        }

        // Sort matches by match score (highest first)
        usort($matches, function($a, $b) {
            return $b->match_score - $a->match_score;
        });

        return $matches;
    }

    private function calculateMatchScore(FundabilityScore $fundabilityScore, Lender $lender): int
    {
        $score = 0;
        $creditScore = $fundabilityScore->credit_score ?? 0;

        // Allow matches within 50 points below minimum (with penalty)
        $tolerance = 50;
        $effectiveMinScore = $lender->min_credit_score - $tolerance;

        // Check if credit score is too far below lender's range
        if ($creditScore < $effectiveMinScore) {
            return 0; // No match if more than 50 points below minimum
        }

        // Credit score alignment (0-50 points)
        if ($creditScore >= $lender->min_credit_score && $creditScore <= $lender->max_credit_score) {
            // Within ideal range - full points
            $range = $lender->max_credit_score - $lender->min_credit_score;
            $position = $creditScore - $lender->min_credit_score;
            
            if ($range > 0) {
                // Higher score within range = better match
                $scorePercentage = ($position / $range) * 100;
                $score += min(50, round($scorePercentage / 2));
            } else {
                $score += 50;
            }
        } elseif ($creditScore > $lender->max_credit_score) {
            // Over-qualified is still good
            $score += 50;
        } elseif ($creditScore < $lender->min_credit_score) {
            // Below minimum but within tolerance - reduced points
            $pointsBelow = $lender->min_credit_score - $creditScore;
            $penaltyPercentage = ($pointsBelow / $tolerance) * 100;
            $reducedPoints = 50 - round($penaltyPercentage / 2);
            $score += max(10, $reducedPoints); // Minimum 10 points
        }

        // Fundability score bonus (0-30 points)
        $fundabilityPoints = round(($fundabilityScore->score / 100) * 30);
        $score += $fundabilityPoints;

        // Account health bonus (0-10 points)
        if ($fundabilityScore->total_accounts >= 5) {
            $score += 10;
        } elseif ($fundabilityScore->total_accounts >= 3) {
            $score += 6;
        } elseif ($fundabilityScore->total_accounts >= 1) {
            $score += 3;
        }

        // Penalty for hard inquiries (0-5 points deduction)
        if ($fundabilityScore->hard_inquiries > 6) {
            $score -= 5;
        } elseif ($fundabilityScore->hard_inquiries > 4) {
            $score -= 3;
        } elseif ($fundabilityScore->hard_inquiries > 2) {
            $score -= 1;
        }

        // Penalty for negative items (0-5 points deduction)
        if ($fundabilityScore->negative_items > 10) {
            $score -= 5;
        } elseif ($fundabilityScore->negative_items > 5) {
            $score -= 3;
        } elseif ($fundabilityScore->negative_items > 2) {
            $score -= 1;
        }

        // Ensure score is between 0-100
        return max(0, min(100, $score));
    }

    /**
     * Determine approval likelihood based on match score.
     */
    private function determineApprovalLikelihood(int $matchScore): string
    {
        if ($matchScore >= 70) {
            return 'high';
        } elseif ($matchScore >= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Estimate APR range for the user with this lender.
     */
    private function estimateApr(FundabilityScore $fundabilityScore, Lender $lender): array
    {
        $creditScore = $fundabilityScore->credit_score ?? 0;
        $baseMin = $lender->min_apr ?? 5.99;
        $baseMax = $lender->max_apr ?? 29.99;

        // Adjust based on credit score
        if ($creditScore >= 750) {
            // Excellent credit gets best rates
            return [
                'min' => $baseMin,
                'max' => $baseMin + (($baseMax - $baseMin) * 0.3),
            ];
        } elseif ($creditScore >= 700) {
            // Good credit
            return [
                'min' => $baseMin + (($baseMax - $baseMin) * 0.2),
                'max' => $baseMin + (($baseMax - $baseMin) * 0.5),
            ];
        } elseif ($creditScore >= 650) {
            // Fair credit
            return [
                'min' => $baseMin + (($baseMax - $baseMin) * 0.4),
                'max' => $baseMin + (($baseMax - $baseMin) * 0.7),
            ];
        } elseif ($creditScore >= 600) {
            // Poor credit
            return [
                'min' => $baseMin + (($baseMax - $baseMin) * 0.6),
                'max' => $baseMin + (($baseMax - $baseMin) * 0.9),
            ];
        } else {
            // Very poor credit gets highest rates
            return [
                'min' => $baseMin + (($baseMax - $baseMin) * 0.7),
                'max' => $baseMax,
            ];
        }
    }

    private function generateMatchReasons(FundabilityScore $fundabilityScore, Lender $lender, int $matchScore): array
    {
        $reasons = [];
        $creditScore = $fundabilityScore->credit_score ?? 0;

        // Credit score fit
        if ($creditScore >= $lender->min_credit_score) {
            if ($creditScore >= $lender->min_credit_score + 50) {
                $reasons[] = "Your credit score exceeds their minimum requirement";
            } else {
                $reasons[] = "Your credit score meets their minimum requirement";
            }
        } elseif ($creditScore >= $lender->min_credit_score - 50) {
            // Below minimum but within tolerance
            $pointsNeeded = $lender->min_credit_score - $creditScore;
            $reasons[] = "You're {$pointsNeeded} points below their preferred minimum - consider as a stretch goal";
        }

        // Lender type benefits
        switch ($lender->type) {
            case 'bank':
                $reasons[] = "Traditional bank with established reputation";
                break;
            case 'credit_union':
                $reasons[] = "Member-focused with competitive rates";
                break;
            case 'online':
                $reasons[] = "Fast online application process";
                break;
            case 'alternative':
                $reasons[] = "Flexible approval criteria";
                break;
        }

        // Fundability score
        if ($fundabilityScore->score >= 70) {
            $reasons[] = "Your strong fundability score increases approval odds";
        } elseif ($fundabilityScore->score >= 50) {
            $reasons[] = "Your fundability score shows lending potential";
        } else {
            $reasons[] = "Focus on improving your fundability score for better approval odds";
        }

        // Account history
        if ($fundabilityScore->total_accounts >= 5) {
            $reasons[] = "Established credit history";
        } elseif ($fundabilityScore->total_accounts < 3) {
            $reasons[] = "Building credit history will improve your chances";
        }

        // Low inquiries
        if ($fundabilityScore->hard_inquiries <= 2) {
            $reasons[] = "Few recent credit inquiries";
        } elseif ($fundabilityScore->hard_inquiries > 4) {
            $reasons[] = "Consider waiting 6 months for inquiries to age";
        }

        return $reasons;
    }
}
