<?php

namespace App\Services;

class AuditService
{
    // Color constants matching the frontend theme
    const COLOR_TEAL = '#0FA99C';
    const COLOR_DANGER = '#E0553F';
    const COLOR_WARN = '#D69A2D';
    const COLOR_MUTED = '#8B879A';
    const COLOR_CHARCOAL = '#3A3844';

    /**
     * Compute the credit audit score (18 to 98)
     */
    public function computeScore(array $a): int
    {
        $s = 72; // Start score
        $negatives = $a['negatives'] ?? [];

        // Score range offset
        $scoreRange = $a['score'] ?? 'unknown';
        if ($scoreRange === 'sub580') {
            $s -= 20;
        } elseif ($scoreRange === '580_669') {
            $s -= 11;
        } elseif ($scoreRange === '740plus') {
            $s += 10;
        } elseif ($scoreRange === '670_739') {
            $s += 3;
        }

        // Negatives deductions
        if (in_array('collections', $negatives)) {
            $s -= 10;
        }
        if (in_array('chargeoffs', $negatives)) {
            $s -= 9;
        }
        if (in_array('lates', $negatives)) {
            $lateCount = $a['late_count'] ?? 'one_two';
            $s -= ($lateCount === 'three_plus') ? 10 : 5;
        }
        if (in_array('repo', $negatives)) {
            $s -= 9;
        }
        if (in_array('inquiries', $negatives)) {
            $s -= 2;
        }

        // Utilization levers
        $util = $a['utilization'] ?? 'nocards';
        if ($util === 'maxed') {
            $s -= 11;
        } elseif ($util === '30to70') {
            $s -= 6;
        } elseif ($util === 'under10') {
            $s += 6;
        }

        // Account mix levers
        $mix = $a['mix'] ?? 'none';
        if ($mix === 'none' || $util === 'nocards') {
            $s -= 7;
        } elseif ($mix === '3plus') {
            $s += 5;
        }

        // Cap score between 18 and 98
        return max(18, min(98, $s));
    }

    /**
     * Get the label and color tier for a given score
     */
    public function getScoreLabel(int $score): array
    {
        if ($score >= 82) {
            return ['Wealth-Building Phase', self::COLOR_TEAL];
        }
        if ($score >= 64) {
            return ['Growth Phase', self::COLOR_CHARCOAL];
        }
        if ($score >= 46) {
            return ['Strengthening Phase', self::COLOR_WARN];
        }
        return ['Foundation Phase', self::COLOR_MUTED];
    }

    /**
     * Compute profile complexity (low, medium, high)
     */
    public function computeComplexity(array $a, array $findings): string
    {
        $score = 0;
        $negatives = $a['negatives'] ?? [];

        if (in_array('repo', $negatives)) {
            $score += 2;
        }
        if (($a['late_count'] ?? '') === 'three_plus') {
            $score += 1;
        }
        if (($a['co_count'] ?? '') === 'two_plus') {
            $score += 2;
        }
        if (in_array(($a['co_status'] ?? ''), ['unsure', 'activeold'])) {
            $score += 1;
        }

        // Count severe (impact = 3) findings
        $severeCount = 0;
        foreach ($findings as $f) {
            if (($f['impact'] ?? 0) === 3) {
                $severeCount++;
            }
        }

        if ($severeCount >= 4) {
            $score += 2;
        }
        if (count($negatives) >= 4) {
            $score += 1;
        }

        if ($score >= 3) {
            return 'high';
        }
        if ($score >= 1) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Build the list of strategic audit findings
     */
    public function buildFindings(array $a): array
    {
        $f = [];
        $negatives = $a['negatives'] ?? [];
        $identifiers = $a['identifiers'] ?? [];

        // Personal Info & Identifiers (Round 1)
        if (in_array('namevar', $identifiers) || in_array('oldaddr', $identifiers) || in_array('otherid', $identifiers)) {
            $items = [];
            if (in_array('namevar', $identifiers)) $items[] = 'name variations';
            if (in_array('oldaddr', $identifiers)) $items[] = 'outdated addresses';
            if (in_array('otherid', $identifiers)) $items[] = 'wrong info on file';

            $f[] = [
                'impact' => 3, 'round' => 1, 'tag' => 'PERSONAL INFO — FIX FIRST', 'tagColor' => self::COLOR_TEAL, 'timeline' => 'Step 1',
                'title' => 'Clean up your personal info before anything else',
                'why' => "This always comes before touching any negative account. " . implode(', ', $items) . " on your file can be connected to how those negative accounts got reported in the first place. Fixing this first isn't a small thing — it can actually weaken the case behind accounts you'll challenge later. Skipping this step is one of the most common mistakes people make.",
            ];
        }

        // Collections (Round 2)
        if (in_array('collections', $negatives)) {
            $f[] = [
                'impact' => 3, 'round' => 2, 'tag' => 'COLLECTIONS — CHALLENGE THESE', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Easy win',
                'title' => 'Challenge your collection accounts — they\'re your easiest win',
                'why' => "Collection accounts usually have the weakest paperwork on your whole report. Debt buyers pay pennies for these accounts and often can't prove they have the right to collect at all. That's why collections get challenged early — right after your personal info is cleaned up.",
            ];
        }

        // Inquiries (Round 1)
        if (in_array('inquiries', $negatives)) {
            $f[] = [
                'impact' => 3, 'round' => 1, 'tag' => 'INQUIRIES — CHALLENGE THESE', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Easy win',
                'title' => 'Challenge your inquiries early — right alongside your personal info',
                'why' => "Inquiries are quick to challenge and don't need much documentation, which is exactly why they get knocked out in the same early batch as your personal info corrections — not saved for later. Getting these cleared early also keeps your file simple while you work on the bigger items.",
            ];
        }

        // Charge-offs (Round 6 / Phase 2)
        if (in_array('chargeoffs', $negatives)) {
            $coCount = $a['co_count'] ?? 'one';
            if ($coCount === 'two_plus') {
                $f[] = [
                    'impact' => 3, 'round' => 6, 'tag' => 'MULTIPLE CHARGE-OFFS — NEEDS REVIEW', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Individual review',
                    'title' => 'Each of your charge-offs needs its own strategy',
                    'why' => "Charge-offs don't all get the same treatment — a fresh one gets left alone, an older one might be ready to challenge, and a couple in between need a closer look before touching anything. With more than one on your report, this can't be answered with a single yes or no. Mapping out the right move for each one, account by account, is exactly what your personalized plan covers.",
                ];
            } else {
                $coStatus = $a['co_status'] ?? 'unsure';
                if ($coStatus === 'under6') {
                    $f[] = [
                        'impact' => 3, 'round' => 6, 'tag' => 'CHARGE-OFF — LET IT AGE', 'tagColor' => self::COLOR_WARN, 'timeline' => 'Let it sit',
                        'title' => 'Don\'t touch your charge-off yet',
                        'why' => "A charge-off under 6 months old is still a fresh, active debt for the original creditor. Disputing it now usually just wakes it up and gets it double-checked while the paperwork is still easy for them to find. The move here is patience, not action.",
                    ];
                } elseif ($coStatus === 'remarked') {
                    $f[] = [
                        'impact' => 3, 'round' => 6, 'tag' => 'CHARGE-OFF — CHALLENGE IT', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Ready to dispute',
                        'title' => 'Good news — your charge-off is ready to dispute',
                        'why' => "When a charge-off is noted as a loss the creditor has written off, that's a real signal worth acting on. It's the kind of detail most people never check — which is exactly why two charge-offs that look identical can need completely different game plans.",
                    ];
                } elseif ($coStatus === 'stopped') {
                    $f[] = [
                        'impact' => 3, 'round' => 6, 'tag' => 'CHARGE-OFF — CHALLENGE IT', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Ready to dispute',
                        'title' => 'Your charge-off has gone quiet — that\'s your opening',
                        'why' => "When a charge-off stops getting updated but is still sitting on your report, that's a sign it's ready to be challenged. Whether an account is actively being watched or not tells you a lot about when to move.",
                    ];
                } elseif ($coStatus === 'activeold') {
                    $f[] = [
                        'impact' => 2, 'round' => 6, 'tag' => 'CHARGE-OFF — NEEDS STRATEGY', 'tagColor' => self::COLOR_WARN, 'timeline' => 'Wait your turn',
                        'title' => 'Your charge-off requires a different approach, not speed',
                        'why' => "It's older, but it's still getting updated every month — meaning someone's paying attention to it. This gets handled later, after the easier wins are done, not as an opening move.",
                    ];
                } else {
                    $f[] = [
                        'impact' => 2, 'round' => 6, 'tag' => 'CHARGE-OFF — CHECK FIRST', 'tagColor' => self::COLOR_WARN, 'timeline' => 'Needs a closer look',
                        'title' => 'We need one more detail about your charge-off',
                        'why' => "Whether this charge-off should be disputed now or left alone depends on details most people never think to check. Until that's confirmed, it stays parked. This is exactly the kind of check Ally runs for you automatically.",
                    ];
                }
            }
        }

        // Late Payments (Round 7 / Phase 3)
        if (in_array('lates', $negatives)) {
            $lateCount = $a['late_count'] ?? 'one_two';
            if ($lateCount === 'three_plus') {
                $f[] = [
                    'impact' => 2, 'round' => 7, 'tag' => 'LATE PAYMENT — DIFFERENT APPROACH', 'tagColor' => self::COLOR_WARN, 'timeline' => 'Pay it down',
                    'title' => 'Your late payment isn\'t a dispute — it\'s a payoff play',
                    'why' => "Three or more lates on one account usually means the reporting is accurate, and you can't dispute away something that's true. The stronger move is paying it down, getting it closed, and asking directly for it to be removed. Disputing this would likely just waste effort for nothing.",
                ];
            } else {
                $f[] = [
                    'impact' => 2, 'round' => 7, 'tag' => 'LATE PAYMENT — HANDLE LAST', 'tagColor' => self::COLOR_MUTED, 'timeline' => 'Handle carefully',
                    'title' => 'Your late payment comes last — on purpose',
                    'why' => "Whether a late payment gets updated is entirely up to the creditor, and disputing too much across your file can hurt your credibility on everything else. This gets handled carefully, after everything else has already moved.",
                ];
            }
        }

        // Repossessions (Round 6)
        if (in_array('repo', $negatives)) {
            $f[] = [
                'impact' => 3, 'round' => 6, 'tag' => 'REPOSSESSION — NEEDS PAPERWORK', 'tagColor' => self::COLOR_DANGER, 'timeline' => 'Needs paperwork',
                'title' => 'Your repo is a paperwork fight — and that favors you',
                'why' => "Repossessions come with strict paperwork requirements: notices, sale records, the math on what's owed after the sale. Mistakes are common — but this has to be built carefully, since a sloppy first move can burn your best shot. This is often best done with direct support.",
            ];
        }

        // Card utilization lever
        $util = $a['utilization'] ?? 'nocards';
        if (in_array($util, ['maxed', '30to70'])) {
            $f[] = [
                'impact' => 3, 'round' => 0, 'tag' => 'CREDIT CARD BALANCES — FASTEST WIN', 'tagColor' => self::COLOR_TEAL, 'timeline' => '~30 days',
                'title' => 'Pay down your credit cards — this moves your score fastest',
                'why' => "This runs at the same time as everything else — you don't have to wait. Lower how much of your card limits you're using, and your score can respond on the very next update. It's the quickest lever here, full stop.",
            ];
        }

        // Thin file mix level
        $mix = $a['mix'] ?? 'none';
        if ($mix === 'none' || $util === 'nocards') {
            $f[] = [
                'impact' => 3, 'round' => 0, 'tag' => 'PRIMARY ACCOUNTS — ADD THESE', 'tagColor' => self::COLOR_TEAL, 'timeline' => 'Start now',
                'title' => 'Add primary accounts — disputing alone won\'t build your profile',
                'why' => "Removing negative items cleans up your report — it doesn't build it up. Without active, positive accounts, even a perfectly clean report stalls out. This runs alongside your dispute work, not after it.",
            ];
        } elseif ($mix === '1to2') {
            $f[] = [
                'impact' => 1, 'round' => 0, 'tag' => 'PRIMARY ACCOUNTS — ADD MORE', 'tagColor' => self::COLOR_TEAL, 'timeline' => '60–90 days',
                'title' => 'Add more primary accounts — your file is thin',
                'why' => "With only 1–2 accounts reporting, one small change can swing your score a lot. Adding the right positive accounts smooths that out, so the work you're doing elsewhere actually sticks.",
            ];
        }

        // Goals overlays
        $goal = $a['goal'] ?? 'health';
        if ($goal === 'home') {
            $f[] = [
                'impact' => 2, 'round' => 0, 'tag' => 'MORTGAGE TIMING', 'tagColor' => self::COLOR_TEAL, 'timeline' => 'Before you apply',
                'title' => 'Mortgage timing changes everything',
                'why' => "Lenders can see open disputes, and that can pause your approval. If buying a home is the goal, everything here needs to be finished and settled before you apply — not happening at the same time.",
            ];
        } elseif ($goal === 'funding') {
            $f[] = [
                'impact' => 2, 'round' => 0, 'tag' => 'FUNDING TIMING', 'tagColor' => self::COLOR_TEAL, 'timeline' => 'At the same time',
                'title' => 'Funding-ready looks different than mortgage-ready',
                'why' => "Business lenders look past the score at how you use your cards and how long your accounts have been open. This gets built a little differently than if you were prepping for a mortgage.",
            ];
        }

        // Sort findings: high impact first, then earlier rounds first
        usort($f, function ($x, $y) {
            $diff = $y['impact'] - $x['impact'];
            if ($diff !== 0) return $diff;
            return $x['round'] - $y['round'];
        });

        return $f;
    }
}
