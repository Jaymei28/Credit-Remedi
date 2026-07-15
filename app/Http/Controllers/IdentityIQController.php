<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\IdentityIQParserService;
use App\Models\CreditReport;
use App\Models\CreditScore;
use App\Models\CreditAccount;
use App\Models\CreditInquiry;
use App\Models\CreditPublicRecord;

class IdentityIQController extends Controller
{
    /**
     * Show the IdentityIQ onboarding page
     */
    public function showOnboarding()
    {
        $user = Auth::user();

        // If already completed onboarding, redirect to dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('identityiq-onboarding');
    }

    /**
     * Confirm user already has IdentityIQ account
     */
    public function confirmExisting(Request $request)
    {
        $user = Auth::user();

        $user->update([
            'identityiq_enrolled' => true,
            'identityiq_enrolled_at' => now(),
        ]);

        // Redirect back with enrolled parameter to show step 2
        return redirect()->route('identityiq.onboarding', ['enrolled' => 'true'])
            ->with('success', 'Great! Now please upload your first IdentityIQ credit report.');
    }

    /**
     * Handle initial credit report upload
     */
    public function uploadInitialReport(Request $request)
    {
        $request->validate([
            'credit_report' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,html,htm|max:10240',
        ]);

        $user = Auth::user();

        try {
            // Store the file
            $file = $request->file('credit_report');
            $fileName = 'initial_report_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Just store the file, skip all parsing for now
            $filePath = $file->storeAs('credit_reports/' . $user->id, $fileName, 'public');

            // Update user record - mark onboarding as complete
            $user->initial_report_uploaded = true;
            $user->initial_report_uploaded_at = now();
            $user->onboarding_completed = true;
            $user->onboarding_completed_at = now();
            
            if (!$user->identityiq_enrolled) {
                $user->identityiq_enrolled = true;
                $user->identityiq_enrolled_at = now();
            }
            
            $user->save();

            // Redirect to dashboard
            return redirect()->route('dashboard')
                ->with('success', '🎉 Setup complete! Your credit report has been uploaded. You can now access all features.');

        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to upload: ' . $e->getMessage());
        }
    }

    /**
     * Skip onboarding (for testing or special cases - admin only)
     */
    public function skipOnboarding(Request $request)
    {
        $user = Auth::user();

        // Only allow admins to skip
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $user->update([
            'identityiq_enrolled' => true,
            'identityiq_enrolled_at' => now(),
            'initial_report_uploaded' => true,
            'initial_report_uploaded_at' => now(),
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('info', 'Onboarding skipped (Admin)');
    }

    /**
     * Get lender rank based on score
     */
    protected function getLenderRank($score)
    {
        if ($score >= 750) return 'Excellent';
        if ($score >= 700) return 'Good';
        if ($score >= 650) return 'Fair';
        if ($score >= 600) return 'Poor';
        return 'Very Poor';
    }
}
