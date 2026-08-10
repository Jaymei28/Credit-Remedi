<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CreditRepairBotController;
use App\Http\Controllers\CreditRepairBotControllerRefactored;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\User;


Route::get('/admin/bypass', function () {
    // Find the admin user
    $admin = User::where('role', 'admin')->first(); // adjust your field if needed

    if (!$admin) {
        return abort(404, 'Admin user not found');
    }

    // Log in the admin
    Auth::login($admin); // same as Auth::attempt without password

    // Regenerate session (same as Laravel login)
    session()->regenerate();

    // Optional: set custom session variables if you want to track bypass
    session([
        'is_admin_bypass' => true,
    ]);

    return redirect()->route('dashboard')->with('message', 'Admin bypass active');
});

Route::post(
    'stripe/webhook',
    [StripeWebhookController::class, 'handleWebhook']
)->name('cashier.webhook');

Route::get('/thank-you', function (Request $request) {
    $destination = $request->query('next', 'dashboard');
    
    if (!auth()->check()) {
        $redirectUrl = url('/login');
    } else {
        $redirectUrl = match ($destination) {
            'onboarding' => route('identityiq.onboarding'),
            default      => url('/dashboard'),
        };
    }
    
    return view('thank-you', compact('redirectUrl'));
})->name('thank-you');

Route::get('/', fn() => view('home'));

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('guest')->group(function () {
    // Forgot password
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset password
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'onboarding'])->name('dashboard');


Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register.submit');

// IdentityIQ Onboarding Routes (must be after registration)
use App\Http\Controllers\IdentityIQController;

Route::middleware('auth')->group(function () {
    Route::get('/identityiq/onboarding', [IdentityIQController::class, 'showOnboarding'])->name('identityiq.onboarding');
    Route::post('/identityiq/confirm-existing', [IdentityIQController::class, 'confirmExisting'])->name('identityiq.confirm-existing');
    Route::post('/identityiq/upload-initial-report', [IdentityIQController::class, 'uploadInitialReport'])->name('identityiq.upload-initial-report');
    Route::post('/identityiq/skip', [IdentityIQController::class, 'skipOnboarding'])->name('identityiq.skip')->middleware('admin');
});


Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login'); // or wherever you want after logout
})->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::get('/disputes/filter', [CreditRepairBotController::class, 'filter'])->name('disputes.filter')->middleware(['auth', 'onboarding']);


Route::middleware('auth')->group(function () {
    Route::get('/subscribe', [SubscriptionController::class, 'show']);
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscribe');
});

Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Show the Phased Dispute Dashboard
Route::get('/credit-repair-bot', [CreditRepairBotController::class, 'showDashboard'])->name('credit-repair-bot')->middleware(['auth', 'onboarding', 'premium']);

// Questionnaire routes
Route::get('/credit-reports/questionnaire', [CreditRepairBotController::class, 'showQuestionnaire'])->name('credit-reports.questionnaire')->middleware(['auth', 'onboarding']);
Route::post('/credit-reports/questionnaire', [CreditRepairBotController::class, 'saveQuestionnaireData'])->name('credit-reports.saveQuestionnaire')->middleware(['auth', 'onboarding']);

// Review & Approve routes
Route::get('/credit-reports/review', [CreditRepairBotController::class, 'showReviewScreen'])->name('credit-reports.review')->middleware(['auth', 'onboarding']);
Route::post('/credit-reports/review', [CreditRepairBotController::class, 'saveReviewData'])->name('credit-reports.saveReview')->middleware(['auth', 'onboarding']);

// Creditor autocomplete API
Route::get('/api/creditors/search', [CreditRepairBotController::class, 'searchCreditors'])->name('api.creditors.search')->middleware(['auth', 'premium']);

Route::get('/my-disputes', [CreditRepairBotController::class, 'index'])->name('disputes.index')->middleware(['auth', 'onboarding']);
Route::get('/my-disputes/{id}', [CreditRepairBotController::class, 'show'])->name('disputes.show')->middleware(['auth', 'onboarding']);
Route::patch('/disputes/{id}/toggle-post', [CreditRepairBotController::class, 'togglePost'])
    ->name('disputes.togglePost')
    ->middleware('auth');

Route::patch('/disputes/{id}/update-letter', [CreditRepairBotController::class, 'updateLetter'])
->name('disputes.updateLetter')
->middleware('auth');

Route::get('/disputes/{id}/download', [CreditRepairBotController::class, 'downloadPdf'])->name('disputes.downloadPdf')->middleware('auth');

Route::post('/disputes/{id}/follow-up', [CreditRepairBotController::class, 'generateFollowUp'])->name('disputes.generateFollowUp')->middleware('auth');

Route::get('/disputes/{id}/download-followup', [CreditRepairBotController::class, 'downloadFollowUpPdf'])->name('disputes.downloadFollowUpPdf')->middleware('auth');

Route::patch('/disputes/{id}/update-sent', [CreditRepairBotController::class, 'updateSent'])->name('disputes.updateSent')->middleware('auth');

Route::get('/resource-center', function () {
    return view('resource-center');
})->name('resource-center')->middleware(['auth', 'onboarding']);

Route::get('/loader-demo', function () {
    return view('loader-demo');
})->name('loader-demo')->middleware('auth');

Route::post('/credit/analyze', [CreditRepairBotController::class, 'analyze'])->name('credit.analyze');

Route::get('/waitlist', [WaitlistController::class, 'create'])->name('waitlist.create');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');

Route::get('/waitlist-report', [WaitlistController::class, 'report'])->name('admin.waitlist.report')->middleware(['auth', 'admin']);
Route::get('/referrals/{code}', [WaitlistController::class, 'publicReferrals'])->name('waitlist.public.referrals');

Route::get('/plans', function () {
    return view('plans');
})->name('plans');

Route::post('/unsubscribe', [SubscriptionController::class, 'unsubscribe'])->name('unsubscribe');

// Tour completion route
Route::post('/tour/complete', [DashboardController::class, 'completeTour'])->name('tour.complete')->middleware('auth');

// Tour reset/replay route
Route::post('/tour/reset', [DashboardController::class, 'resetTour'])->name('tour.reset')->middleware('auth');


Route::middleware('auth')->group(function () {
    // Upload form page
    Route::get('/credit-reports/upload', function () {
        return view('credit-reports.upload');
    })->name('credit-reports.uploadPage');

    // Handle upload
    Route::post('/credit-reports/upload', [CreditRepairBotController::class, 'upload'])->name('credit-reports.upload');

    // Show single report
    Route::get('/credit-reports/{id}', [CreditRepairBotController::class, 'showReport'])->name('credit-reports.show');

    // Generate action plan
    Route::post('/credit-reports/{id}/action-plan', [CreditRepairBotController::class, 'generateActionPlan'])->name('credit-reports.actionPlan');
});

// Credit Data API for Dispute Pre-fill
use App\Http\Controllers\CreditDataController;

Route::middleware(['auth'])->group(function () {
    Route::get('/api/credit-data', [CreditDataController::class, 'getCreditData'])->name('api.credit-data');
    Route::get('/api/credit-data/account/{id}', [CreditDataController::class, 'getAccountDetails'])->name('api.credit-data.account');
});

// IdentityIQ Import Routes
use App\Http\Controllers\IdentityIQImportController;

Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/identityiq/import', [IdentityIQImportController::class, 'index'])->name('identityiq.import');
    Route::post('/identityiq/import', [IdentityIQImportController::class, 'import'])->name('identityiq.import.process');
    Route::get('/identityiq/reports/{id}', [IdentityIQImportController::class, 'show'])->name('identityiq.report.show');
    Route::delete('/identityiq/reports/{id}', [IdentityIQImportController::class, 'destroy'])->name('identityiq.report.delete');
});

// AI Analysis Routes
use App\Http\Controllers\AIAnalysisController;

Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::post('/ai-analysis/run', [AIAnalysisController::class, 'analyze'])->name('ai-analysis.run');
    Route::get('/ai-analysis/results/{id?}', [AIAnalysisController::class, 'showResults'])->name('ai-analysis.results');
});

// Fundability Score & Lender Matching Routes
use App\Http\Controllers\FundabilityScoreController;

Route::middleware(['auth', 'onboarding', App\Http\Middleware\CheckProAccess::class])->group(function () {
    Route::get('/fundability-score', [FundabilityScoreController::class, 'index'])->name('fundability.index');
    Route::post('/fundability-score/calculate', [FundabilityScoreController::class, 'calculate'])->name('fundability.calculate');
    Route::get('/fundability-score/results', [FundabilityScoreController::class, 'results'])->name('fundability.results');
});

// Credit Builders Routes
use App\Http\Controllers\CreditBuildersController;

Route::middleware(['auth', 'onboarding'])->group(function () {
    Route::get('/credit-builders', [CreditBuildersController::class, 'index'])->name('credit-builders.index');
});

// Admin Routes
use App\Http\Controllers\Admin\BotPromptController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Bot Prompts Management
    Route::resource('bot-prompts', BotPromptController::class);
    Route::post('bot-prompts/{botPrompt}/toggle', [BotPromptController::class, 'toggle'])->name('bot-prompts.toggle');
    Route::post('bot-prompts-clear-cache', [BotPromptController::class, 'clearCache'])->name('bot-prompts.clear-cache');
});

// Debug Check Route (remove after debugging)
Route::get('/debug-check', function () {
    $output = '<h1>Server Debug Information</h1>';
    
    $output .= '<h2>1. PHP Version</h2>';
    $output .= 'PHP Version: ' . phpversion() . '<br>';
    
    $output .= '<h2>2. File Existence Check</h2>';
    $output .= 'Laravel Version: ' . app()->version() . '<br>';
    $output .= 'Environment: ' . app()->environment() . '<br>';
    $output .= 'Debug Mode: ' . (config('app.debug') ? 'ON' : 'OFF') . '<br>';
    
    return $output;
});

// Refresh Everything
Route::get('/optimize', function() {
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    return "The server has been COMPLETELY refreshed. Please close all tabs and open a NEW window!";
});
