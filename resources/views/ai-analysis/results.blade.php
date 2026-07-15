@extends('layouts.app')

@section('title', 'AI Analysis Results')

@section('content')

<style>
    /* Dark Theme Overrides */
    body {
        background-color: #1a1d29;
    }

    .results-container {
        background-color: #1a1d29;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    /* Header Card */
    .header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }

    .header-card h3 {
        color: white;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-card p {
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        font-size: 1rem;
    }

    .back-btn {
        background: white;
        color: #667eea;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: #667eea;
    }

    /* Credit Scores Section */
    .scores-section {
        background: #252836;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .section-title {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Circular Gauge */
    .score-gauge-container {
        text-align: center;
        position: relative;
    }

    .circular-gauge {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto 1rem;
    }

    .gauge-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .gauge-bg circle {
        fill: none;
        stroke: #2d3142;
        stroke-width: 12;
    }

    .gauge-progress {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .gauge-progress circle {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease;
    }

    .gauge-score {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
    }

    .bureau-name {
        color: white;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }

    .score-badge {
        display: inline-block;
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-excellent { background: #10b981; color: white; }
    .badge-good { background: #10b981; color: white; }
    .badge-fair { background: #f59e0b; color: white; }
    .badge-poor { background: #ef4444; color: white; }
    .badge-very-poor { background: #dc2626; color: white; }

    .average-score {
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid #2d3142;
        margin-top: 2rem;
    }

    .average-score h5 {
        color: white;
        font-size: 1.25rem;
        margin: 0;
    }

    .average-score .score-value {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 1.5rem;
    }

    /* Summary Cards */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-card {
        background: #252836;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid transparent;
        transition: all 0.3s;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    .summary-card.info { border-color: #3b82f6; }
    .summary-card.success { border-color: #10b981; }
    .summary-card.danger { border-color: #ef4444; }
    .summary-card.warning { border-color: #f59e0b; }

    .summary-card .icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .summary-card .icon.info { color: #3b82f6; }
    .summary-card .icon.success { color: #10b981; }
    .summary-card .icon.danger { color: #ef4444; }
    .summary-card .icon.warning { color: #f59e0b; }

    .summary-card .label {
        color: #9ca3af;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .summary-card .value {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
    }

    /* Tables */
    .data-table-section {
        background: #252836;
        border-radius: 16px;
        margin-bottom: 2rem;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .data-table-section.negative { border-color: #ef4444; }
    .data-table-section.positive { border-color: #10b981; }
    .data-table-section.warning { border-color: #f59e0b; }

    .table-header {
        padding: 1.5rem 2rem;
        color: white;
    }

    .table-header.negative { background: #ef4444; }
    .table-header.positive { background: #10b981; }
    .table-header.warning { background: #f59e0b; }

    .table-header h5 {
        margin: 0 0 0.25rem 0;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-header small {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .data-table {
        width: 100%;
        color: white;
    }

    .data-table thead {
        background: #1f2230;
    }

    .data-table th {
        padding: 1rem 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        border-bottom: 1px solid #2d3142;
    }

    .data-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #2d3142;
        color: #e5e7eb;
    }

    .data-table tbody tr {
        transition: background-color 0.2s;
    }

    .data-table tbody tr:hover {
        background: #2d3142;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* AI Recommendation Column Styling */
    .ai-recommendation {
        min-width: 300px;
        max-width: 400px;
    }

    .ai-recommendation-box {
        background: rgba(239, 68, 68, 0.1);
        border-left: 4px solid #ef4444;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        line-height: 1.5;
        color: #f3f4f6;
    }

    .ai-recommendation-box i {
        color: #fbbf24;
        margin-right: 0.5rem;
        font-size: 1rem;
    }


    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.danger { background: #ef4444; color: white; }
    .status-badge.success { background: #10b981; color: white; }
    .status-badge.warning { background: #f59e0b; color: white; }

    .action-btn {
        padding: 0.6rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .action-btn:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
    }

    /* Recommended Steps */
    .steps-section {
        background: #252836;
        border-radius: 16px;
        padding: 2rem;
    }

    .step-item {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .step-item:last-child {
        margin-bottom: 0;
    }

    .step-number {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.25rem;
    }

    .step-content h6 {
        color: white;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .step-content p {
        color: #9ca3af;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .step-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .step-btn.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .step-btn.secondary {
        background: #10b981;
        color: white;
    }

    .step-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .circular-gauge {
            width: 120px;
            height: 120px;
        }

        .gauge-score {
            font-size: 2rem;
        }

        .summary-cards {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .summary-card {
            padding: 1.25rem;
        }

        .summary-card .value {
            font-size: 2rem;
        }

        /* Mobile Table to Cards */
        .data-table thead {
            display: none;
        }

        .data-table,
        .data-table tbody,
        .data-table tr,
        .data-table td {
            display: block;
            width: 100%;
        }

        .data-table tr {
            background: #1f2230;
            margin-bottom: 1rem;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #2d3142;
        }

        .data-table td {
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #2d3142;
            position: relative;
            padding-left: 45%;
        }

        .data-table td:last-child {
            border-bottom: none;
        }

        .data-table td:before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: 40%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .ai-recommendation {
            min-width: auto;
            max-width: none;
        }

        .ai-recommendation-box {
            font-size: 0.85rem;
            padding: 0.65rem 0.85rem;
        }

        .action-btn {
            width: 100%;
            justify-content: center;
            padding: 0.75rem;
        }

        .header-card h3 {
            font-size: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 480px) {
        .summary-cards {
            grid-template-columns: 1fr;
        }

        .results-container {
            padding: 1rem 0.5rem;
        }
    }

</style>

<div class="results-container">
    <div class="container">
        
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header --}}
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>
                        <i class="bi bi-robot"></i>
                        AI Analysis Results
                    </h3>
                    <p>Your credit report has been analyzed and categorized</p>
                </div>
                <a href="{{ route('dashboard') }}" class="back-btn">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Credit Scores Section --}}
        @if($creditScores->count() > 0)
        <div class="scores-section">
            <h5 class="section-title">
                <i class="bi bi-graph-up-arrow"></i>
                Credit Scores
            </h5>
            
            <div class="row">
                @foreach($creditScores as $score)
                @php
                    $percentage = (($score->score - 300) / 550) * 100;
                    $circumference = 2 * 3.14159 * 70;
                    $offset = $circumference - ($percentage / 100) * $circumference;
                    
                    // Determine color based on score
                    if ($score->score >= 750) {
                        $color = '#10b981';
                        $badgeClass = 'badge-excellent';
                    } elseif ($score->score >= 700) {
                        $color = '#10b981';
                        $badgeClass = 'badge-good';
                    } elseif ($score->score >= 650) {
                        $color = '#f59e0b';
                        $badgeClass = 'badge-fair';
                    } elseif ($score->score >= 600) {
                        $color = '#ef4444';
                        $badgeClass = 'badge-poor';
                    } else {
                        $color = '#dc2626';
                        $badgeClass = 'badge-very-poor';
                    }
                @endphp
                <div class="col-md-4 mb-4">
                    <div class="score-gauge-container">
                        <div class="circular-gauge">
                            <svg class="gauge-bg" viewBox="0 0 160 160">
                                <circle cx="80" cy="80" r="70"></circle>
                            </svg>
                            <svg class="gauge-progress" viewBox="0 0 160 160">
                                <circle cx="80" cy="80" r="70" 
                                        stroke="{{ $color }}"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $offset }}">
                                </circle>
                            </svg>
                            <div class="gauge-score">{{ $score->score }}</div>
                        </div>
                        <div class="bureau-name">{{ $score->bureau }}</div>
                        <span class="score-badge {{ $badgeClass }}">{{ $score->lender_rank }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            @if($averageScore)
            <div class="average-score">
                <h5>Average Score: <span class="score-value">{{ $averageScore }}</span></h5>
            </div>
            @endif
        </div>
        @endif

        {{-- Summary Cards --}}
        <div class="summary-cards">
            <div class="summary-card info">
                <div class="icon info">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="label">Total Accounts</div>
                <div class="value">{{ $accounts->count() }}</div>
            </div>

            <div class="summary-card success">
                <div class="icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="label">Open Accounts</div>
                <div class="value">{{ $openAccounts->count() }}</div>
            </div>

            <div class="summary-card danger">
                <div class="icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="label">Negative Items</div>
                <div class="value">{{ $negativeAccounts->count() }}</div>
            </div>

            <div class="summary-card warning">
                <div class="icon warning">
                    <i class="bi bi-search"></i>
                </div>
                <div class="label">Hard Inquiries</div>
                <div class="value">{{ $inquiries->count() }}</div>
            </div>
        </div>

        {{-- Negative Accounts Table --}}
        @if($negativeAccounts->count() > 0)
        <div class="data-table-section negative">
            <div class="table-header negative">
                <h5>
                    <i class="bi bi-exclamation-triangle"></i>
                    Negative Accounts ({{ $negativeAccounts->count() }})
                </h5>
                <small>These items may be hurting your credit score</small>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Creditor</th>
                            <th>Account Type</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>AI Recommendation</th>
                            <th>Bureau</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($negativeAccounts as $account)
                        <tr>
                            <td class="fw-semibold" data-label="Creditor">{{ $account->creditor_name }}</td>
                            <td data-label="Account Type">{{ $account->account_type }}</td>
                            <td data-label="Status">
                                <span class="status-badge danger">{{ $account->account_status }}</span>
                            </td>
                            <td data-label="Balance">${{ number_format($account->current_balance, 2) }}</td>
                            <td class="ai-recommendation" data-label="AI Recommendation">
                                <div class="ai-recommendation-box">
                                    <i class="bi bi-lightbulb-fill"></i>
                                    {{ $account->remarks ?? 'No specific recommendation provided.' }}
                                </div>
                            </td>
                            <td data-label="Bureau">{{ $account->bureau ?? 'All Bureaus' }}</td>
                            <td data-label="Action">
                                <a href="{{ route('credit-repair-bot') }}?dispute=true&creditor={{ urlencode($account->creditor_name) }}&account_type={{ urlencode($account->account_type) }}&status={{ urlencode($account->account_status) }}&bureau={{ urlencode($account->bureau ?? 'All Bureaus') }}&reason={{ urlencode($account->remarks ?? 'Inaccurate information') }}" class="action-btn">
                                    <i class="bi bi-file-earmark-plus me-1"></i>Dispute
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Open Accounts Table --}}
        @if($openAccounts->count() > 0)
        <div class="data-table-section positive">
            <div class="table-header positive">
                <h5>
                    <i class="bi bi-check-circle"></i>
                    Open Accounts ({{ $openAccounts->count() }})
                </h5>
                <small>These accounts are in good standing</small>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Creditor</th>
                            <th>Account Type</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Credit Limit</th>
                            <th>Bureau</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openAccounts as $account)
                        <tr>
                            <td class="fw-semibold">{{ $account->creditor_name }}</td>
                            <td>{{ $account->account_type }}</td>
                            <td>
                                <span class="status-badge success">{{ $account->account_status }}</span>
                            </td>
                            <td>${{ number_format($account->current_balance, 2) }}</td>
                            <td>${{ number_format($account->credit_limit, 2) }}</td>
                            <td>{{ $account->bureau ?? 'All Bureaus' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Hard Inquiries Table --}}
        @if($inquiries->count() > 0)
        <div class="data-table-section warning">
            <div class="table-header warning">
                <h5>
                    <i class="bi bi-search"></i>
                    Hard Inquiries ({{ $inquiries->count() }})
                </h5>
                <small>Recent credit checks that may impact your score</small>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Creditor</th>
                            <th>Date</th>
                            <th>Bureau</th>
                            <th>Impact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inquiries as $inquiry)
                        <tr>
                            <td class="fw-semibold">{{ $inquiry->creditor_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($inquiry->inquiry_date)->format('M d, Y') }}</td>
                            <td>{{ $inquiry->bureau ?? 'All Bureaus' }}</td>
                            <td>
                                @php
                                    $daysOld = \Carbon\Carbon::parse($inquiry->inquiry_date)->diffInDays(now());
                                    if ($daysOld < 180) {
                                        echo '<span class="status-badge danger">High</span>';
                                    } elseif ($daysOld < 365) {
                                        echo '<span class="status-badge warning">Medium</span>';
                                    } else {
                                        echo '<span class="status-badge success">Low</span>';
                                    }
                                @endphp
                            </td>
                            <td>
                                <a href="{{ route('credit-repair-bot') }}?dispute=true&creditor={{ urlencode($inquiry->creditor_name) }}&account_type=Hard Inquiry&status=Inquiry&bureau={{ urlencode($inquiry->bureau ?? 'All Bureaus') }}&reason=Unauthorized inquiry" class="action-btn">
                                    <i class="bi bi-file-earmark-plus me-1"></i>Dispute
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Public Records Table --}}
        @if($publicRecords->count() > 0)
        <div class="data-table-section negative">
            <div class="table-header negative">
                <h5>
                    <i class="bi bi-file-earmark-ruled"></i>
                    Public Records ({{ $publicRecords->count() }})
                </h5>
                <small>Legal records affecting your credit</small>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Record Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publicRecords as $record)
                        <tr>
                            <td class="fw-semibold">{{ $record->record_type }}</td>
                            <td>
                                <span class="status-badge danger">{{ $record->status }}</span>
                            </td>
                            <td>${{ number_format($record->amount, 2) }}</td>
                            <td>{{ $record->date_filed ? \Carbon\Carbon::parse($record->date_filed)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('credit-repair-bot') }}?dispute=true&creditor={{ urlencode($record->record_type) }}&account_type=Public Record&status={{ urlencode($record->status) }}&bureau=All Bureaus&reason=Inaccurate public record" class="action-btn">
                                    <i class="bi bi-file-earmark-plus me-1"></i>Dispute
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Recommended Next Steps --}}
        <div class="steps-section">
            <h5 class="section-title">
                <i class="bi bi-lightbulb"></i>
                Recommended Next Steps
            </h5>

            @if($negativeAccounts->count() > 0 || $inquiries->count() > 0)
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h6>Dispute Negative Items with Ally</h6>
                    <p>Review the details of the negative accounts identified and chat with Ally AI to initiate a dispute process for any inaccuracies or outdated information.</p>
                    <a href="{{ route('credit-repair-bot') }}" class="step-btn primary">
                        <i class="bi bi-robot"></i>
                        Chat with Ally to Dispute
                    </a>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-dismiss alerts
    document.addEventListener('DOMContentLoaded', () => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush
