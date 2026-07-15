@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Include Ally Guided Tour --}}
@include('components.ally-tour')

<style>
    /* Progress Bar Styles */
    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: var(--bg-tertiary);
        overflow: hidden;
    }

    .progress-bar-custom {
        background: var(--gradient-primary);
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    /* Activity Feed Styles */
    .activity-item {
        position: relative;
        padding-left: 2.5rem;
        padding-bottom: 1.5rem;
        border-left: 2px solid var(--border-color);
    }

    .activity-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }

    .activity-icon {
        position: absolute;
        left: -0.75rem;
        top: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
    }

    /* Checklist Styles */
    .checklist-item {
        padding: 0.75rem 1rem;
        border-radius: var(--border-radius-md);
        transition: all var(--transition-base);
        cursor: pointer;
    }

    .checklist-item:hover {
        background: var(--bg-secondary);
    }

    .checklist-item.completed {
        opacity: 0.6;
    }

    .checklist-item.completed .btn {
        opacity: 1 !important;
    }

    .checklist-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-base);
    }

    .checklist-item.completed .checklist-checkbox {
        background: var(--gradient-success);
        border-color: transparent;
    }

    /* Stats Card Animation */
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-number {
        animation: countUp 0.5s ease-out;
    }

    /* Mobile Responsive - Smaller Cards */
    @media (max-width: 768px) {
        /* Reduce card padding */
        .card-body {
            padding: 1rem !important;
        }

        /* Smaller icons */
        .card-body i.fs-3 {
            font-size: 1.5rem !important;
            margin-bottom: 0.5rem !important;
        }

        /* Smaller stat numbers */
        .stat-number {
            font-size: 1.75rem !important;
        }

        /* Smaller card titles */
        .card-title.text-uppercase.small {
            font-size: 0.7rem !important;
        }

        /* Smaller text below stats */
        .card-body small {
            font-size: 0.75rem !important;
        }

        /* Reduce gap between stat cards */
        .row.g-3 {
            gap: 0.75rem !important;
        }

        /* Make stat cards more compact */
        .col-md-3.col-sm-6 .card {
            margin-bottom: 0;
        }

        /* Smaller buttons in checklist */
        .checklist-item .btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem !important;
            white-space: nowrap !important;
            min-width: auto !important;
        }

        /* Smaller checklist text */
        .checklist-item p {
            font-size: 0.85rem !important;
            margin-bottom: 0.25rem !important;
        }

        /* Smaller checklist small text */
        .checklist-item small {
            font-size: 0.75rem !important;
        }

        /* Smaller checklist checkbox */
        .checklist-checkbox {
            width: 1rem !important;
            height: 1rem !important;
            min-width: 1rem !important;
        }

        /* Adjust checklist item spacing */
        .checklist-item {
            padding: 0.5rem 0.75rem !important;
        }

        /* Smaller quick action buttons */
        .btn-md {
            padding: 0.5rem 1rem !important;
            font-size: 0.85rem !important;
        }
    }
</style>

<div class="container px-2 px-md-3 mb-5">

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center fw-semibold auto-dismiss-alert" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show text-center fw-semibold auto-dismiss-alert" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-center fw-semibold auto-dismiss-alert" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Personalized Welcome Banner --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-gradient shadow-strong">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-2">
                                @php
                                    $hour = date('H');
                                    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                                @endphp
                                {{ $greeting }}, {{ Auth::user()->name }}! 👋
                            </h3>
                            <p class="text-white mb-0 opacity-90">
                                Here's your credit journey progress. You're doing great!
                            </p>
                            <button id="replayTourBtn" class="btn btn-sm btn-light mt-3" style="opacity: 0.9;">
                                <i class="bi bi-arrow-clockwise me-1"></i> 
                                {{ auth()->user()->tour_completed ? 'Replay Ally Tour' : 'Reset Ally Tour' }}
                            </button>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi Logo" style="max-width: 120px; height: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role === 'admin')
        {{-- Admin Dashboard --}}
        <div class="row mb-4 g-3">
            <!-- Metric Cards -->
            <div class="col-lg-8">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-6">
                        <div class="card card-border-primary shadow-soft hover-lift">
                            <div class="card-body text-center">
                                <i class="bi bi-people-fill text-primary fs-3 mb-2"></i>
                                <h6 class="card-title text-uppercase small mb-1 text-muted">Total Users</h6>
                                <h2 class="fw-bold mb-0 gradient-text stat-number">{{ $userCount }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="card card-border-info shadow-soft hover-lift">
                            <div class="card-body text-center">
                                <i class="bi bi-folder-check text-info fs-3 mb-2"></i>
                                <h6 class="card-title text-uppercase small mb-1 text-muted">Total Disputes</h6>
                                <h2 class="fw-bold mb-0 gradient-text stat-number">{{ $disputesFiled }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="card card-border-warning shadow-soft hover-lift">
                            <div class="card-body text-center">
                                <i class="bi bi-hourglass-split text-warning fs-3 mb-2"></i>
                                <h6 class="card-title text-uppercase small mb-1 text-muted">Pending Letters</h6>
                                <h2 class="fw-bold mb-0 stat-number">{{ $pendingDisputes }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Users Table -->
            <div class="col-lg-4">
                <div class="card shadow-soft h-100 hover-lift">
                    <div class="card-header">
                        <h6 class="mb-0">👥 Top Users by Letters Filed</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0 table-dark">
                            <thead style="background-color: #2a3f5f;">
                                <tr>
                                    <th class="text-start border-bottom border-secondary" style="width: 70%; padding: 0.75rem 1rem; color: #ffffff;">User</th>
                                    <th class="text-end border-bottom border-secondary" style="width: 30%; padding: 0.75rem 1rem; color: #ffffff;">Letters</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topUsers as $user)
                                    <tr>
                                        <td class="text-start border-secondary" style="padding: 0.75rem 1rem; color: #e0e0e0;">{{ $user->name }}</td>
                                        <td class="text-end border-secondary" style="padding: 0.75rem 1rem;">
                                            <span class="badge bg-primary rounded-pill">{{ $user->letter_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- User Dashboard --}}
        
        {{-- Quick Stats Cards --}}
        <div class="row mb-4 g-3">
            <div class="col-md-3 col-sm-6">
                <div class="card card-border-primary shadow-soft hover-lift">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text text-primary fs-3 mb-2"></i>
                        <h6 class="text-uppercase small mb-1 text-muted">Disputes Filed</h6>
                        <h2 class="fw-bold mb-0 gradient-text stat-number">{{ $totalDisputes }}</h2>
                        <small class="text-muted">{{ $totalDisputes > 0 ? 'Keep going!' : 'Start your journey' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-border-success shadow-soft hover-lift">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle text-success fs-3 mb-2"></i>
                        <h6 class="text-uppercase small mb-1 text-muted">Items Removed</h6>
                        <h2 class="fw-bold mb-0 stat-number">{{ $itemsRemoved }}</h2>
                        <small class="text-muted">{{ $itemsRemoved > 0 ? 'Great progress!' : 'Coming soon' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-border-warning shadow-soft hover-lift">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history text-warning fs-3 mb-2"></i>
                        <h6 class="text-uppercase small mb-1 text-muted">Pending</h6>
                        <h2 class="fw-bold mb-0 stat-number">{{ $userPendingDisputes }}</h2>
                        <small class="text-muted">{{ $userPendingDisputes > 0 ? 'In progress' : 'All clear' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-border-info shadow-soft hover-lift">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up-arrow text-info fs-3 mb-2"></i>
                        <h6 class="text-uppercase small mb-1 text-muted">Credit Score</h6>
                        @if($averageCreditScore)
                            <h2 class="fw-bold mb-0 stat-number">{{ $averageCreditScore }}</h2>
                            <small class="text-success">Average Score</small>
                        @else
                            <h2 class="fw-bold mb-0 stat-number">{{ $creditScoreTrend >= 0 ? '+' : '' }}{{ $creditScoreTrend }}</h2>
                            <small class="{{ $creditScoreTrend > 0 ? 'text-success' : 'text-muted' }}">{{ $creditScoreTrend > 0 ? '↑ Trending up' : 'Track your progress' }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Recent Activity Feed --}}
            <div class="col-lg-6">
                <div class="card shadow-soft h-100">
                    <div class="card-header">
                        <h5 class="mb-0">🔔 Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed">
                            @forelse($recentActivity as $activity)
                                <div class="activity-item">
                                    @php
                                        $iconClass = 'bg-primary';
                                        $icon = 'file-earmark-plus';
                                        $title = 'Dispute filed';
                                        
                                        if ($activity->posted_1) {
                                            $iconClass = 'bg-success';
                                            $icon = 'check-circle';
                                            $title = 'Dispute completed';
                                        } elseif ($activity->sent) {
                                            $iconClass = 'bg-info';
                                            $icon = 'send';
                                            $title = 'Dispute sent';
                                        }
                                    @endphp
                                    <div class="activity-icon {{ $iconClass }} text-white">
                                        <i class="bi bi-{{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 fw-semibold">{{ $title }}</p>
                                        <p class="mb-0 small text-muted">
                                            {{ $activity->credit_item_type ?? 'Dispute' }} - {{ $activity->creditor_name ?? 'Creditor' }}
                                        </p>
                                        <small class="text-muted">{{ $activity->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No activity yet</p>
                                    <small class="text-muted">File your first dispute to get started!</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('disputes.index') }}" class="btn btn-sm btn-outline-primary w-100">View All Activity</a>
                    </div>
                </div>
            </div>

            {{-- Onboarding Checklist --}}
            <div class="col-lg-6">
                <div class="card shadow-soft h-100">
                    <div class="card-header">
                        <h5 class="mb-0">✅ Getting Started Checklist</h5>
                        <small class="text-muted">Complete these steps to maximize your results</small>
                    </div>
                    <div class="card-body">
                        <div class="checklist">
                            <div class="checklist-item {{ $onboardingSteps['account_created'] ? 'completed' : '' }} mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="checklist-checkbox me-3">
                                        @if($onboardingSteps['account_created'])
                                            <i class="bi bi-check text-white"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold">Create your account</p>
                                        <small class="text-muted">Welcome to Credit Remedi!</small>
                                    </div>
                                </div>
                            </div>
                            <div class="checklist-item {{ $onboardingSteps['report_uploaded'] ? 'completed' : '' }} mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="checklist-checkbox me-3">
                                        @if($onboardingSteps['report_uploaded'])
                                            <i class="bi bi-check text-white"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold">Upload your credit report</p>
                                        <small class="text-muted">{{ $onboardingSteps['report_uploaded'] ? 'Great! We can now analyze it' : 'Add your credit report to vault' }}</small>
                                    </div>
                                    @if(!$onboardingSteps['report_uploaded'])
                                        <a href="{{ route('identityiq.import') }}" class="btn btn-sm btn-outline-primary">Upload</a>
                                    @endif
                                </div>
                            </div>
                            <div class="checklist-item {{ $onboardingSteps['ai_analysis_run'] ? 'completed' : '' }} mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="checklist-checkbox me-3">
                                        @if($onboardingSteps['ai_analysis_run'])
                                            <i class="bi bi-check text-white"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1" style="{{ $onboardingSteps['ai_analysis_run'] ? 'opacity: 1;' : '' }}">
                                        <p class="mb-0 fw-semibold">Run AI analysis</p>
                                        @php
                                            $hasPaidPlan = in_array(auth()->user()->plan_type, ['starter', 'standard', 'pro', 'premium']) || auth()->user()->role === 'admin';
                                        @endphp
                                        @if ($hasPaidPlan)
                                            <small class="text-muted">Let AI identify disputable items</small>
                                        @else
                                            <small class="text-warning">⭐ Paid feature - <a href="{{ route('plans') }}" class="text-warning fw-bold">Upgrade to unlock</a></small>
                                        @endif
                                    </div>
                                    @if ($hasPaidPlan)
                                        @if(!$onboardingSteps['ai_analysis_run'])
                                            <form action="{{ route('ai-analysis.run') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-gradient-primary">Start</button>
                                            </form>
                                        @else
                                            @php
                                                $latestReport = \App\Models\CreditReport::where('user_id', auth()->id())
                                                    ->latest()
                                                    ->first();
                                            @endphp
                                            @if($latestReport)
                                                <a href="{{ route('ai-analysis.results', ['id' => $latestReport->id]) }}" 
                                                   class="btn btn-success text-white fw-bold shadow-sm" 
                                                   style="opacity: 1 !important; filter: none !important;">
                                                    <i class="bi bi-eye me-1"></i>View Results
                                                </a>
                                            @endif
                                        @endif
                                    @else
                                        <a href="{{ route('plans') }}" class="btn btn-sm btn-warning">Upgrade</a>
                                    @endif
                                </div>
                            </div>
                            <div class="checklist-item {{ $onboardingSteps['first_dispute_filed'] ? 'completed' : '' }} mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="checklist-checkbox me-3">
                                        @if($onboardingSteps['first_dispute_filed'])
                                            <i class="bi bi-check text-white"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold">File your first dispute</p>
                                        <small class="text-muted">{{ $onboardingSteps['first_dispute_filed'] ? 'Great job! Keep going' : 'Take action on negative items' }}</small>
                                    </div>
                                    @if(!$onboardingSteps['first_dispute_filed'])
                                        <a href="{{ route('disputes.index') }}" class="btn btn-sm btn-outline-primary">Go</a>
                                    @endif
                                </div>
                            </div>
                            <div class="checklist-item {{ $onboardingSteps['resources_explored'] ? 'completed' : '' }} mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="checklist-checkbox me-3">
                                        @if($onboardingSteps['resources_explored'])
                                            <i class="bi bi-check text-white"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold">Explore resources</p>
                                        <small class="text-muted">Learn agencies to file disputes for deletions.</small>
                                    </div>
                                    @if(!$onboardingSteps['resources_explored'])
                                        <a href="{{ route('resource-center') }}" class="btn btn-sm btn-outline-primary">Learn</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">{{ $completedSteps }} of {{ $totalSteps }} completed</span>
                            <span class="small fw-semibold text-primary">{{ $onboardingProgress }}% Complete</span>
                        </div>
                        <div class="progress-custom mt-2">
                            <div class="progress-bar-custom" style="width: {{ $onboardingProgress }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Credit Report Summary Widget --}}
        <div class="row mt-4">
            <div class="col-12">
                @include('components.credit-report-widget')
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-soft">
                    <div class="card-header">
                        <h5 class="mb-0">⚡ Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        @if (in_array(auth()->user()->plan_type, ['starter', 'standard', 'pro', 'premium']) || auth()->user()->role === 'admin')
                            {{-- Pro/Premium Plan: 6 buttons --}}
                            <div class="row g-3 justify-content-center">
                                <div class="col-lg col-md-4 col-sm-6 text-center">
                                    <a href="{{ route('disputes.index') }}" class="btn btn-gradient-primary btn-md w-100">
                                        <i class="bi bi-folder-check me-2"></i> My Disputes
                                    </a>
                                </div>
                                <div class="col-lg col-md-4 col-sm-6 text-center">
                                    <a href="{{ route('credit-repair-bot') }}" class="btn btn-md w-100 fw-semibold shadow-soft border-0" style="background: linear-gradient(135deg, #00C8C8 0%, #00A0A0 100%); color: #000;">
                                        <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="height: 20px; width: 20px;"> Chat with Ally
                                    </a>
                                </div>
                                @if (in_array(auth()->user()->plan_type, ['pro', 'premium']) || auth()->user()->role === 'admin')
                                    <div class="col-lg col-md-4 col-sm-6 text-center">
                                        <a href="{{ route('fundability.index') }}" class="btn btn-md w-100 fw-semibold shadow-soft border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;">
                                            <i class="bi bi-graph-up-arrow me-2"></i> Fundability
                                        </a>
                                    </div>
                                @endif
                                <div class="col-lg col-md-4 col-sm-6 text-center">
                                    <a href="{{ route('credit-vault') }}" class="btn btn-gradient-info btn-md w-100">
                                        <i class="bi bi-list-check me-2"></i> Credit Vault
                                    </a>
                                </div>
                                @if (in_array(auth()->user()->plan_type, ['pro', 'premium']) || auth()->user()->role === 'admin')
                                    <div class="col-lg col-md-4 col-sm-6 text-center">
                                        <a href="https://www.skool.com/credit-remedy-academy-5068/about?ref=cd2596c36ce54883a4a1a876af63413a" target="_blank" class="btn btn-md w-100 fw-semibold shadow-soft border-0" style="background: linear-gradient(135deg, #9333EA 0%, #7C3AED 100%); color: #fff;">
                                            <i class="bi bi-people-fill me-2"></i> Community
                                        </a>
                                    </div>
                                @endif
                                <div class="col-lg col-md-4 col-sm-6 text-center">
                                    <a href="mailto:help@creditremedi.com?subject=Need Support - Credit Remedi Dashboard" class="btn btn-md w-100 fw-semibold shadow-soft border-0" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff;">
                                        <i class="bi bi-envelope-fill me-2"></i> Contact Support
                                    </a>
                                </div>
                            </div>
                        @else
                            {{-- Standard Plan: 3 buttons centered --}}
                            <div class="row g-3 justify-content-center">
                                <div class="col-md-4">
                                    <a href="{{ route('disputes.index') }}" class="btn btn-gradient-primary btn-md w-100">
                                        <i class="bi bi-folder-check me-2"></i> My Disputes
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('credit-vault') }}" class="btn btn-gradient-info btn-md w-100">
                                        <i class="bi bi-list-check me-2"></i> Credit Vault
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="mailto:help@creditremedi.com?subject=Need Support - Credit Remedi Dashboard" class="btn btn-md w-100 fw-semibold shadow-soft border-0" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff;">
                                        <i class="bi bi-envelope-fill me-2"></i> Contact Support
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Auto-dismiss flash notifications after 1 second
    document.addEventListener('DOMContentLoaded', () => {
        const alerts = document.querySelectorAll('.auto-dismiss-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000); // 5 seconds
        });

        // Animate progress bars on load
        const progressBars = document.querySelectorAll('.progress-bar-custom');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });

        // Replay Tour Button Handler
        const replayBtn = document.getElementById('replayTourBtn');
        if (replayBtn) {
            replayBtn.addEventListener('click', function() {
                if (confirm('This will restart the Ally guided tour. Continue?')) {
                    // Show loading state
                    replayBtn.disabled = true;
                    replayBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spinner-border spinner-border-sm"></i> Resetting...';
                    
                    fetch('/tour/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to show tour
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error resetting tour:', error);
                        alert('Failed to reset tour. Please try again.');
                        replayBtn.disabled = false;
                        replayBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Replay Ally Tour';
                    });
                }
            });
        }
    });
</script>
@endpush
