@extends('layouts.app')

@section('title', 'Lender Matches')

@section('content')
<style>
    .lender-card {
        background: var(--bg-primary);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        border: 2px solid var(--border-color);
    }

    .lender-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        border-color: #667eea;
    }

    .lender-card.top-match {
        border: 2px solid #10b981;
        background: var(--bg-primary);
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
    }

    .match-score-badge {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .match-score-badge.high {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .match-score-badge.medium {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .match-score-badge.low {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .apr-range {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
    }

    .apr-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }

    .match-reason {
        display: flex;
        align-items: start;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .match-reason i {
        color: #10b981;
        font-size: 1.1rem;
        margin-top: 2px;
    }

    .top-match-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .summary-stat {
        text-align: center;
        padding: 1rem;
    }

    .summary-stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .summary-stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: 2px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--text-secondary);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-tab:hover {
        border-color: #667eea;
        color: #667eea;
    }

    .filter-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }
</style>

<div class="container px-3 mb-5">
    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ route('fundability.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    {{-- Summary Card --}}
    <div class="summary-card">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <h2 class="mb-2">🎯 Your Lender Matches</h2>
                <p class="mb-0 opacity-90">
                    Based on your fundability score of <strong>{{ $fundabilityScore->score }}</strong> (Grade {{ $fundabilityScore->grade }}), 
                    we've found <strong>{{ $matches->count() }}</strong> lenders that match your profile.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-4">
                        <div class="summary-stat">
                            <div class="summary-stat-number">{{ $matches->where('approval_likelihood', 'high')->count() }}</div>
                            <div class="summary-stat-label">High Match</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="summary-stat">
                            <div class="summary-stat-number">{{ $matches->where('approval_likelihood', 'medium')->count() }}</div>
                            <div class="summary-stat-label">Medium Match</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="summary-stat">
                            <div class="summary-stat-number">{{ $matches->where('approval_likelihood', 'low')->count() }}</div>
                            <div class="summary-stat-label">Low Match</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all">
            All Matches ({{ $matches->count() }})
        </button>
        <button class="filter-tab" data-filter="high">
            High Approval ({{ $matches->where('approval_likelihood', 'high')->count() }})
        </button>
        <button class="filter-tab" data-filter="medium">
            Medium Approval ({{ $matches->where('approval_likelihood', 'medium')->count() }})
        </button>
        <button class="filter-tab" data-filter="low">
            Lower Approval ({{ $matches->where('approval_likelihood', 'low')->count() }})
        </button>
    </div>

    {{-- Lender Cards --}}
    <div id="lender-matches">
        @forelse ($matches as $index => $match)
            <div class="lender-card {{ $index < 3 ? 'top-match' : '' }} match-item" data-likelihood="{{ $match->approval_likelihood }}">
                <div class="row align-items-center">
                    {{-- Match Score --}}
                    <div class="col-lg-2 col-md-3 text-center mb-3 mb-md-0">
                        <div class="match-score-badge {{ $match->approval_likelihood }}">
                            {{ $match->match_score }}
                        </div>
                        <small class="d-block mt-2 fw-semibold text-{{ $match->getLikelihoodBadgeColor() }}">
                            {{ $match->getFormattedLikelihood() }}
                        </small>
                    </div>

                    {{-- Lender Info --}}
                    <div class="col-lg-6 col-md-9 mb-3 mb-lg-0">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <h4 class="mb-0">{{ $match->lender->name }}</h4>
                            @if ($index < 3)
                                <span class="top-match-badge">
                                    <i class="bi bi-star-fill"></i> Top Match
                                </span>
                            @endif
                        </div>
                        <p class="text-muted mb-2">{{ $match->lender->description }}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-{{ $match->lender->getTypeBadgeColor() }}">
                                {{ $match->lender->getFormattedType() }}
                            </span>
                            <span class="badge bg-secondary">
                                ${{ number_format($match->lender->min_amount, 0) }} - ${{ number_format($match->lender->max_amount, 0) }}
                            </span>
                        </div>
                    </div>

                    {{-- APR Range --}}
                    <div class="col-lg-4">
                        <div class="apr-range">
                            <small class="text-muted d-block mb-1">Estimated APR Range</small>
                            <div class="apr-number">
                                {{ number_format($match->estimated_apr_min, 2) }}% - {{ number_format($match->estimated_apr_max, 2) }}%
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Match Reasons --}}
                <div class="mt-3">
                    <h6 class="mb-2">Why this is a good match:</h6>
                    <div class="row">
                        @foreach ($match->match_reasons as $reason)
                            <div class="col-md-6">
                                <div class="match-reason">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>{{ $reason }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Requirements --}}
                @if (!empty($match->lender->requirements))
                    <div class="mt-3">
                        <h6 class="mb-2">Requirements:</h6>
                        <ul class="small text-muted mb-0">
                            @foreach ($match->lender->requirements as $requirement)
                                <li>{{ $requirement }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Apply Button --}}
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Match Score: {{ $match->match_score }}/100
                        </small>
                        @if ($match->lender->application_url)
                            <a href="{{ $match->lender->application_url }}" target="_blank" class="btn btn-gradient-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Apply Now
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                Application Coming Soon
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-soft">
                <div class="card-body text-center p-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #d1d5db;"></i>
                    <h4 class="mt-3 mb-2">No Lender Matches Found</h4>
                    <p class="text-muted">
                        We couldn't find any matching lenders at this time. Try improving your fundability score and check back later.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Disclaimer --}}
    <div class="card shadow-soft mt-4">
        <div class="card-body">
            <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i> Important Information</h6>
            <small class="text-muted">
                <ul class="mb-0">
                    <li>These matches are estimates based on your current credit profile and are not guarantees of approval.</li>
                    <li>Actual APR rates and approval decisions are determined by the lender based on their underwriting criteria.</li>
                    <li>Applying for credit may result in a hard inquiry on your credit report.</li>
                    <li>Credit Remedi is not a lender and does not guarantee loan approval or specific rates.</li>
                </ul>
            </small>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', () => {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const matchItems = document.querySelectorAll('.match-item');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Filter matches
                const filter = tab.dataset.filter;
                matchItems.forEach(item => {
                    if (filter === 'all' || item.dataset.likelihood === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection
