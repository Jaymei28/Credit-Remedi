<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DisputeLetter;
use App\Models\CreditReport;
use App\Models\CreditScore;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $userCount = User::count();
            $paidUsers = User::where('has_paid', true)->sum('paid_amount');


            $disputesFiled = DisputeLetter::count();
            $pendingDisputes = DisputeLetter::where('posted_1', false)->count();

            $topUsers = User::select('users.id', 'users.name', DB::raw('COUNT(dispute_letters.id) as letter_count'))
            ->join('dispute_letters', 'users.id', '=', 'dispute_letters.user_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('letter_count')
            ->take(6) // optional: top 6
            ->get();
            


            return view('dashboard', compact(
                'userCount',
                'paidUsers',
                'disputesFiled',
                'pendingDisputes',
                'topUsers'
            ));
        }

        // For regular users - provide dynamic stats
        $userId = $user->id;
        
        // Get user's dispute statistics
        $userDisputes = DisputeLetter::where('user_id', $userId);
        $totalDisputes = $userDisputes->count();
        $pendingDisputes = $userDisputes->clone()->where('posted_1', false)->count();
        $sentDisputes = $userDisputes->clone()->where('sent', true)->count();
        
        // Calculate items removed (Logic moved below $latestReport definition)
        // ... handled below ...
        $itemsRemoved = 0; 
        
        // Get recent activity (last 5 disputes, ordered by most recently updated)
        $recentActivity = DisputeLetter::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        // Calculate onboarding progress
        $onboardingSteps = [
            'account_created' => true, // Always true if logged in
            'report_uploaded' => CreditReport::where('user_id', $userId)->exists(),
            'ai_analysis_run' => $user->ai_analysis_completed ?? false,
            'first_dispute_filed' => $totalDisputes > 0,
            'resources_explored' => false, // You can track this with a separate table
        ];
        
        $completedSteps = count(array_filter($onboardingSteps));
        $totalSteps = count($onboardingSteps);
        $onboardingProgress = round(($completedSteps / $totalSteps) * 100);
        
        // Calculate progress metrics
        $disputesProgress = min(($totalDisputes / 10) * 100, 100); // Assuming 10 is the target
        $itemsRemovedProgress = min(($itemsRemoved / 5) * 100, 100); // Assuming 5 is the target
        $profileProgress = 75; // You can calculate this based on user profile completion
        
        // Calculate credit score trend (placeholder - you can integrate actual credit score tracking)
        $creditScoreTrend = 0; // Default to 0, update when you have credit score data
        $averageCreditScore = null;
        
        // Get latest credit report and scores
        $latestReport = CreditReport::where('user_id', $userId)
            ->latest()
            ->first();
        
        $creditScores = collect();
        $accountsCount = 0;
        
        if ($latestReport) {
            $creditScores = CreditScore::where('credit_report_id', $latestReport->id)->get();
            $accountsCount = $latestReport->creditAccounts()->count();
            
            // Calculate average credit score
            if ($creditScores->count() > 0) {
                $averageCreditScore = round($creditScores->avg('score'));
            }

            // Calculate items removed (verified only when a new report is imported)
            // COMPARING FIRST REPORT VS CURRENT REPORT
            $firstReport = CreditReport::where('user_id', $userId)->oldest()->first();
            if ($firstReport && $latestReport && $firstReport->id !== $latestReport->id) {
                $initialNegatives = $firstReport->negative_accounts_count ?? 0;
                $currentNegatives = $latestReport->negative_accounts_count ?? 0;
                $itemsRemoved = max(0, $initialNegatives - $currentNegatives);
            }
        }
        
        return view('dashboard', [
            'userCount' => null,
            'paidUsers' => null,
            'disputesFiled' => null,
            'pendingDisputes' => null,
            'topUsers' => null,
            // User-specific data
            'totalDisputes' => $totalDisputes,
            'userPendingDisputes' => $pendingDisputes,
            'sentDisputes' => $sentDisputes,
            'itemsRemoved' => $itemsRemoved,
            'recentActivity' => $recentActivity,
            'onboardingSteps' => $onboardingSteps,
            'completedSteps' => $completedSteps,
            'totalSteps' => $totalSteps,
            'onboardingProgress' => $onboardingProgress,
            'disputesProgress' => $disputesProgress,
            'itemsRemovedProgress' => $itemsRemovedProgress,
            'profileProgress' => $profileProgress,
            'creditScoreTrend' => $creditScoreTrend,
            'averageCreditScore' => $averageCreditScore,
            // Credit report data
            'latestReport' => $latestReport,
            'creditScores' => $creditScores,
            'accountsCount' => $accountsCount,
        ]);
    }

    /**
     * Mark the guided tour as completed for the authenticated user
     */
    public function completeTour(Request $request)
    {
        $user = auth()->user();
        $user->tour_completed = true;
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Reset the guided tour so user can replay it
     */
    public function resetTour(Request $request)
    {
        $user = auth()->user();
        $user->tour_completed = false;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Tour reset! Refresh the page to start the tour again.']);
    }
}
