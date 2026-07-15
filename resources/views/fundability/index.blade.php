@extends('layouts.app')

@section('title', 'Fundability Score & Lender Matching')

@section('content')
<style>
    .fundability-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .fundability-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .score-circle {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        margin: 0 auto 2rem;
    }

    .score-number {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .score-grade {
        font-size: 1.5rem;
        font-weight: 700;
        color: #6b7280;
        margin-top: 0.5rem;
    }

    .factor-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .factor-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .factor-progress {
        height: 8px;
        background: var(--bg-tertiary);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .factor-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        transition: width 1s ease;
    }

    .recommendation-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .recommendation-card.high-priority {
        background: var(--bg-secondary);
        border-left-color: #f59e0b;
    }

    .strength-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0.25rem;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .weakness-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0.25rem;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .cta-button {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        font-size: 5rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
</style>

<div class="container px-3 mb-5">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (!$hasScore)
        {{-- Empty State - No Score Yet --}}
        <div class="fundability-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="mb-3">🎯 Fundability Score & Lender Matching</h1>
                    <p class="lead mb-4">
                        Discover your funding potential and get matched with lenders who are most likely to approve you.
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Comprehensive fundability analysis</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Personalized lender recommendations</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Estimated approval odds & rates</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Actionable improvement tips</li>
                    </ul>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="bi bi-graph-up-arrow" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>

        <div class="card shadow-soft">
            <div class="card-body p-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <h3 class="mb-3">Ready to Calculate Your Fundability Score?</h3>
                    <p class="text-muted mb-4">
                        We'll analyze your credit profile and match you with lenders based on your unique financial situation.
                    </p>
                    
                    <form action="{{ route('fundability.calculate') }}" method="POST">
                        @csrf
                        <button type="submit" class="cta-button">
                            <i class="bi bi-calculator me-2"></i> Calculate My Score
                        </button>
                    </form>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            This analysis uses your IdentityIQ report data and dispute history
                        </small>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Has Score - Show Dashboard --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-soft">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">Your Fundability Score</h2>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Last updated {{ $fundabilityScore->updated_at->diffForHumans() }}
                                </p>
                            </div>
                            <form action="{{ route('fundability.calculate') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Recalculate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- Score Overview --}}
            <div class="col-lg-4">
                <div class="card shadow-soft h-100">
                    <div class="card-body text-center p-4">
                        <div class="score-circle">
                            <div class="score-number">{{ $fundabilityScore->score }}</div>
                            <div class="score-grade">Grade {{ $fundabilityScore->grade }}</div>
                        </div>
                        <h5 class="mb-3">Fundability Score</h5>
                        <p class="text-muted">
                            @if ($fundabilityScore->score >= 80)
                                Excellent! You have strong funding potential.
                            @elseif ($fundabilityScore->score >= 70)
                                Good! You're in a solid position for approval.
                            @elseif ($fundabilityScore->score >= 60)
                                Fair. Some improvements could boost your odds.
                            @elseif ($fundabilityScore->score >= 50)
                                Needs work. Focus on the recommendations below.
                            @else
                                Significant improvement needed. Start with credit repair.
                            @endif
                        </p>
                        <a href="{{ route('fundability.results') }}" class="btn btn-gradient-primary w-100 mt-3">
                            <i class="bi bi-bank me-2"></i> View Lender Matches
                        </a>
                    </div>
                </div>
            </div>

            {{-- Strengths & Weaknesses --}}
            <div class="col-lg-8">
                <div class="card shadow-soft h-100">
                    <div class="card-body p-4">
                        <h5 class="mb-3"><i class="bi bi-trophy me-2 text-success"></i> Your Strengths</h5>
                        <div class="mb-4">
                            @if (!empty($fundabilityScore->strengths))
                                @foreach ($fundabilityScore->strengths as $strength)
                                    <span class="strength-badge">
                                        <i class="bi bi-check-circle-fill"></i> {{ $strength }}
                                    </span>
                                @endforeach
                            @else
                                <p class="text-muted">Keep working on your credit to build strengths!</p>
                            @endif
                        </div>

                        <h5 class="mb-3"><i class="bi bi-exclamation-triangle me-2 text-warning"></i> Areas to Improve</h5>
                        <div>
                            @if (!empty($fundabilityScore->weaknesses))
                                @foreach ($fundabilityScore->weaknesses as $weakness)
                                    <span class="weakness-badge">
                                        <i class="bi bi-x-circle-fill"></i> {{ $weakness }}
                                    </span>
                                @endforeach
                            @else
                                <p class="text-success">Great! No major weaknesses detected.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Score Factors --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-soft">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Score Breakdown</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @if (!empty($fundabilityScore->factors))
                                @foreach ($fundabilityScore->factors as $key => $factor)
                                    <div class="col-md-6">
                                        <div class="factor-card">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">{{ ucwords(str_replace('_', ' ', $key)) }}</h6>
                                                <span class="badge bg-primary">{{ $factor['points'] }}/{{ $factor['max_points'] }} pts</span>
                                            </div>
                                            <div class="factor-progress">
                                                <div class="factor-progress-bar" style="width: {{ $factor['percentage'] }}%"></div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                @if (isset($factor['value']))
                                                    Score: {{ $factor['value'] }}
                                                @elseif (isset($factor['count']))
                                                    Count: {{ $factor['count'] }}
                                                @elseif (isset($factor['total_accounts']))
                                                    {{ $factor['total_accounts'] }} total, {{ $factor['open_accounts'] }} open
                                                @elseif (isset($factor['completed']))
                                                    {{ $factor['completed'] }} completed disputes
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-soft">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i> Personalized Recommendations</h5>
                    </div>
                    <div class="card-body p-4">
                        @if (!empty($fundabilityScore->recommendations))
                            @foreach ($fundabilityScore->recommendations as $recommendation)
                                <div class="recommendation-card {{ $recommendation['priority'] === 'high' ? 'high-priority' : '' }}">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="d-flex align-items-start flex-grow-1">
                                            <div class="me-3">
                                                <i class="bi bi-{{ $recommendation['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $recommendation['title'] }}</h6>
                                                <p class="mb-0 small">{{ $recommendation['description'] }}</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-3">
                                            @if ($recommendation['priority'] === 'high')
                                                <span class="badge bg-warning text-dark">High Priority</span>
                                            @endif
                                            @if(isset($recommendation['action_url']))
                                                <a href="{{ $recommendation['action_url'] }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-arrow-right-circle me-1"></i>{{ $recommendation['action_text'] ?? 'Learn More' }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No specific recommendations at this time. Keep up the good work!</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Animate progress bars on load
    document.addEventListener('DOMContentLoaded', () => {
        const progressBars = document.querySelectorAll('.factor-progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush

@endsection
