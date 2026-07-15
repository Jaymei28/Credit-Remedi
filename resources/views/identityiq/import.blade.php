@extends('layouts.app')

@section('title', 'Import IdentityIQ Report')

@section('content')
<style>
    .import-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .upload-card {
        background: var(--bg-primary, #ffffff);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color, #e5e7eb);
    }
    
    .info-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    [data-bs-theme="dark"] .info-box {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        border-color: #60a5fa;
    }
    
    .info-box h3 {
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    [data-bs-theme="dark"] .info-box h3 {
        color: #93c5fd;
    }
    
    .info-box ol {
        color: #1e40af;
        margin: 0;
        padding-left: 1.5rem;
    }
    
    [data-bs-theme="dark"] .info-box ol {
        color: #bfdbfe;
    }
    
    .info-box li {
        margin-bottom: 0.5rem;
    }
    
    .report-card {
        background: var(--bg-primary, #ffffff);
        border: 2px solid var(--border-color, #e5e7eb);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .report-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
    }
    
    .report-title {
        color: var(--text-primary, #111827);
        font-weight: 600;
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
    }
    
    .report-meta {
        color: var(--text-secondary, #6b7280);
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    
    .report-stats {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .stat-item {
        color: var(--text-secondary, #374151);
        font-size: 0.875rem;
    }
    
    .stat-item strong {
        color: var(--text-primary, #111827);
        font-weight: 700;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        color: white;
    }
    
    .btn-danger-custom {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-danger-custom:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        color: white;
    }
    
    .file-input-wrapper {
        position: relative;
        margin-bottom: 1rem;
    }
    
    .file-input-wrapper input[type="file"] {
        padding: 0.75rem;
        border: 2px solid var(--border-color, #d1d5db);
        border-radius: 8px;
        width: 100%;
        font-size: 0.875rem;
        color: var(--text-primary, #111827);
        background: var(--bg-secondary, #f9fafb);
    }
    
    .file-input-wrapper input[type="file"]:focus {
        outline: none;
        border-color: #3b82f6;
        background: var(--bg-primary, white);
    }
    
    /* Dark mode file input button fix */
    .file-input-wrapper input[type="file"]::file-selector-button {
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-primary, #111827);
        border: 1px solid var(--border-color, #d1d5db);
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .file-input-wrapper input[type="file"]::file-selector-button:hover {
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-primary, #111827);
        border: 1px solid var(--border-color, #d1d5db);
    }
    
    /* Firefox compatibility */
    .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-primary, #111827);
        border: 1px solid var(--border-color, #d1d5db);
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .file-input-wrapper input[type="file"]::-webkit-file-upload-button:hover {
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-primary, #111827);
        border: 1px solid var(--border-color, #d1d5db);
    }
    
    .help-text {
        color: var(--text-secondary, #6b7280) !important;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    .section-title {
        color: var(--text-primary, #111827) !important;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        color: var(--text-primary, #111827) !important;
        font-weight: 600;
    }

</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="import-header">
                <h1 class="mb-2" style="font-size: 2rem; font-weight: 700;">📊 Import IdentityIQ Credit Report</h1>
                <p class="mb-0" style="opacity: 0.95; font-size: 1.05rem;">Upload your IdentityIQ credit report PDF file to automatically extract and analyze your credit data.</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Upload Form -->
            <div class="upload-card">
                <h2 class="section-title">📤 Upload New Report</h2>
                
                <form action="{{ route('identityiq.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="credit_report" class="form-label">
                            Select IdentityIQ Report (PDF file)
                        </label>
                        <div class="file-input-wrapper">
                            <input 
                                type="file" 
                                name="credit_report" 
                                id="credit_report" 
                                accept=".pdf"
                                required
                                class="form-control"
                            >
                        </div>
                        @error('credit_report')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                        <p class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Maximum file size: 20MB. Only PDF files from IdentityIQ are supported.
                        </p>
                    </div>

                    <div class="info-box">
                        <h3><i class="bi bi-lightbulb me-2"></i>How to get your IdentityIQ report:</h3>
                        <ol>
                            <li>Log in to your IdentityIQ account</li>
                            <li>Navigate to your credit report</li>
                            <li>Click "Download" or "Print" -> "Save as PDF"</li>
                            <li>Upload the downloaded PDF file here</li>
                        </ol>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary-custom" id="importBtn">
                            <i class="bi bi-upload me-2"></i>
                            Import Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Previously Imported Reports -->
            @if($creditReports->count() > 0)
                <div class="upload-card">
                    <h2 class="section-title">📋 Previously Imported Reports</h2>
                    
                    @foreach($creditReports as $report)
                        <div class="report-card">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div class="flex-grow-1">
                                    <h3 class="report-title">
                                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                        {{ $report->original_filename }}
                                    </h3>
                                    <p class="report-meta mb-2">
                                        <i class="bi bi-calendar me-1"></i>
                                        Imported on {{ $report->created_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                    <div class="report-stats">
                                        <span class="stat-item">
                                            <i class="bi bi-graph-up text-success me-1"></i>
                                            <strong>{{ $report->creditScores()->count() }}</strong> Credit Scores
                                        </span>
                                        <span class="stat-item">
                                            <i class="bi bi-credit-card text-info me-1"></i>
                                            <strong>{{ $report->total_accounts_count ?? $report->creditAccounts()->count() }}</strong> Accounts
                                        </span>
                                        <span class="stat-item">
                                            <i class="bi bi-search text-warning me-1"></i>
                                            <strong>{{ $report->hard_inquiries_count ?? $report->creditInquiries()->count() }}</strong> Inquiries
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <a 
                                        href="{{ route('identityiq.report.show', $report->id) }}"
                                        class="btn btn-primary-custom btn-sm"
                                    >
                                        <i class="bi bi-eye me-1"></i>
                                        View Details
                                    </a>
                                    @php
                                        $hasPaidPlan = in_array(auth()->user()->plan_type, ['starter', 'standard', 'pro', 'premium']) || auth()->user()->role === 'admin';
                                    @endphp
                                    @if($hasPaidPlan)
                                        @php
                                            // Check if this report has been analyzed (has credit scores/accounts)
                                            $hasAnalysis = $report->creditScores()->exists() || $report->creditAccounts()->exists();
                                        @endphp
                                        
                                        @if($hasAnalysis)
                                            <a 
                                                href="{{ route('ai-analysis.results', $report->id) }}"
                                                class="btn btn-sm"
                                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; font-weight: 600;"
                                            >
                                                <i class="bi bi-robot me-1"></i>
                                                View AI Analysis
                                            </a>
                                        @else
                                            <form action="{{ route('ai-analysis.run') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="report_id" value="{{ $report->id }}">
                                                <button 
                                                    type="submit"
                                                    class="btn btn-sm"
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-weight: 600;"
                                                >
                                                    <i class="bi bi-cpu me-1"></i>
                                                    Run Analysis
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    <form action="{{ route('identityiq.report.delete', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?');" class="mb-0">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            class="btn btn-danger-custom btn-sm"
                                        >
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const importForm = document.getElementById('importForm');
        const importBtn = document.getElementById('importBtn');

        if (importForm && importBtn) {
            importForm.addEventListener('submit', function() {
                // Change button state to loading
                importBtn.disabled = true;
                importBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Importing...
                `;
            });
        }
    });
</script>
@endsection
