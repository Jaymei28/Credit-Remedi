<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditReport;
use App\Models\CreditScore;
use App\Models\CreditAccount;
use Illuminate\Support\Facades\Auth;

class CreditDataController extends Controller
{
    /**
     * Get user's imported credit data for dispute pre-fill
     */
    public function getCreditData(Request $request)
    {
        $user = Auth::user();
        
        // Get the latest credit report
        $latestReport = CreditReport::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latestReport) {
            return response()->json([
                'has_data' => false,
                'message' => 'No credit report found. Please upload your IdentityIQ report first.',
            ]);
        }

        // Get credit scores
        $scores = CreditScore::where('credit_report_id', $latestReport->id)->get();

        // Get accounts (will be populated once parser is enhanced)
        $accounts = CreditAccount::where('credit_report_id', $latestReport->id)->get();

        // Format accounts for easy selection
        $formattedAccounts = $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'creditor_name' => $account->creditor_name,
                'account_number' => $account->account_number ?? 'N/A',
                'account_type' => $account->account_type ?? 'Unknown',
                'account_status' => $account->account_status ?? 'Unknown',
                'current_balance' => $account->current_balance ?? 0,
                'bureau' => $account->bureau ?? 'Unknown',
                'date_opened' => $account->date_opened ? $account->date_opened->format('m/d/Y') : 'N/A',
                'payment_status' => $account->payment_status ?? 'Unknown',
            ];
        });

        return response()->json([
            'has_data' => true,
            'report_date' => $latestReport->created_at->format('M d, Y'),
            'scores' => $scores->map(function ($score) {
                return [
                    'bureau' => $score->bureau,
                    'score' => $score->score,
                    'lender_rank' => $score->lender_rank,
                ];
            }),
            'accounts' => $formattedAccounts,
            'total_accounts' => $accounts->count(),
        ]);
    }

    /**
     * Get account details for pre-filling dispute
     */
    public function getAccountDetails($id)
    {
        $user = Auth::user();
        
        $account = CreditAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'creditor_name' => $account->creditor_name,
            'account_number' => $account->account_number ?? 'N/A',
            'account_type' => $account->account_type ?? 'Unknown',
            'account_status' => $account->account_status,
            'bureau' => $account->bureau,
            'current_balance' => $account->current_balance,
            'date_opened' => $account->date_opened ? $account->date_opened->format('m/d/Y') : null,
            'payment_status' => $account->payment_status,
            'remarks' => $account->remarks,
        ]);
    }
}
