<style>
    /* Credit Report Widget Dark Mode Support */
    .credit-score-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        transition: background 0.3s ease;
    }
    
    [data-theme="dark"] .credit-score-card {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d4a6f 100%);
        border-color: #3b82f6 !important;
    }
    
    [data-theme="dark"] .alert-info {
        background: #1e3a5f !important;
        border-color: #3b82f6 !important;
        color: #93c5fd !important;
    }
    
    [data-theme="dark"] .alert-info .alert-link {
        color: #93c5fd !important;
    }
</style>

@if(isset($latestReport) && $latestReport)
<div class="card shadow-sm mb-4" style="border-left: 4px solid #3b82f6;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-file-earmark-bar-graph text-primary"></i>
                Your Credit Report Summary
            </h5>
            <small class="text-muted">Imported: {{ $latestReport->created_at->format('M d, Y') }}</small>
        </div>

        @if(isset($creditScores) && $creditScores->count() > 0)
        <div class="row mb-3">
            <div class="col-12 mb-2">
                <h6 class="text-muted mb-0"><strong>Credit Scores:</strong></h6>
            </div>
            @foreach($creditScores as $score)
            <div class="col-md-4 mb-2">
                <div class="p-3 rounded credit-score-card" style="border: 1px solid #3b82f6;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $score->bureau }}</small>
                            <h4 class="mb-0 fw-bold
                                @if(in_array($score->lender_rank, ['Excellent', 'Good'])) text-success
                                @elseif($score->lender_rank == 'Fair') text-warning
                                @else text-danger
                                @endif
                            " style="font-size: 2rem;">{{ $score->score }}</h4>
                        </div>
                        <span class="badge 
                            @if(in_array($score->lender_rank, ['Excellent', 'Good'])) bg-success
                            @elseif($score->lender_rank == 'Fair') bg-warning
                            @else bg-danger
                            @endif
                        " style="font-size: 0.7rem;">{{ $score->lender_rank }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(isset($accountsCount) && $accountsCount > 0)
        <div class="alert alert-info mb-3" style="background: #f0f9ff; border-color: #3b82f6; color: #1e40af;">
            <i class="bi bi-info-circle"></i>
            <strong>{{ $accountsCount }} account{{ $accountsCount > 1 ? 's' : '' }}</strong> found in your credit report.
            <a href="{{ route('identityiq.report.show', $latestReport->id) }}" class="alert-link" style="color: #1e40af; text-decoration: underline;">View details →</a>
        </div>
        @endif

        <div class="d-grid gap-2">
            <a href="{{ route('credit-repair-bot') }}" class="btn btn-primary">
               <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="height: 20px; width: 20px;">
               Let Ally Generate My Dispute Letters
            </a>
            <a href="{{ route('identityiq.import') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-upload"></i> Upload New Report
            </a>
        </div>
    </div>
</div>
@else
<div class="card shadow-sm mb-4" style="border-left: 4px solid #f59e0b;">
    <div class="card-body">
        <h5 class="card-title">
            <i class="bi bi-exclamation-triangle text-warning"></i>
            No Credit Report Found
        </h5>
        <p class="text-muted mb-3">Upload your IdentityIQ credit report to get started with automated dispute letter generation.</p>
        <a href="{{ route('identityiq.import') }}" class="btn btn-warning">
            <i class="bi bi-upload"></i> Upload Your First Report
        </a>
    </div>
</div>
@endif
