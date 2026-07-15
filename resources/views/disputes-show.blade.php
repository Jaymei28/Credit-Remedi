@extends('layouts.app')

@section('title', 'Dispute Details')

@section('content')

@push('styles')
<style>
    /* Disputes Details Styling */
    .dispute-header-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Letter Preview */
    .letter-preview {
        background: #fff; /* Always white for paper look */
        color: #333; /* Always dark text */
        border: 1px solid var(--border-color);
        padding: 3rem;
        font-family: 'Times New Roman', Times, serif;
        font-size: 1.1rem;
        line-height: 1.6;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        min-height: 600px;
        white-space: pre-wrap;
    }

    .dark-mode .letter-preview {
        border: 1px solid #444; /* Darker border in dark mode */
        background: #fff; /* Keep paper white even in dark mode */
        color: #000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }
    
    /* Timeline Section */
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-item::before {
        /* Line handled by border-start on the item itself now */
        display: none;
    }

    /* 🌙 DARK MODE OVERRIDES FOR DETAILS PAGE */
    [data-theme="dark"] .bg-white {
        background-color: var(--bg-primary) !important;
        color: var(--text-primary) !important;
    }

    [data-theme="dark"] .card {
        background-color: var(--bg-primary) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .card-header {
        background-color: var(--bg-secondary) !important;
        border-bottom-color: var(--border-color) !important;
    }

    [data-theme="dark"] .card-header h6 {
        color: var(--text-primary) !important;
    }

    [data-theme="dark"] .text-dark {
        color: var(--text-primary) !important;
    }

    /* Keep Letter Preview White (Paper) */
    [data-theme="dark"] .letter-preview {
        background-color: #ffffff !important;
        color: #000000 !important;
        border-color: #4a5568 !important;
    }
    
    /* Timeline Fixes */
    [data-theme="dark"] .timeline-item .fw-bold.text-dark {
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .border-start {
        border-left-color: #4a5568 !important;
    }

    /* Form & Input Fixes */
    [data-theme="dark"] .bg-light {
        background-color: var(--bg-secondary) !important;
    }
    
    [data-theme="dark"] .form-control {
        background-color: var(--bg-primary) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }

    [data-theme="dark"] .form-text {
        color: var(--text-muted) !important;
    }

    /* Modal Fixes */
    [data-theme="dark"] .modal-content {
        background-color: var(--bg-primary) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .modal-header {
        border-bottom-color: var(--border-color) !important;
    }
    
    [data-theme="dark"] .modal-footer {
        border-top-color: var(--border-color) !important;
    }
    
    [data-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* Title Colors */
    [data-theme="dark"] h4.text-dark {
        color: #ffffff !important;
    }
</style>
@endpush

<div class="container py-4 mt-3 mb-5 px-3 px-md-4"> 
    {{-- Top Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('disputes.index') }}" class="btn btn-light border btn-sm shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Dispute Details</h4>
                <div class="text-muted small">Manage and track your dispute letter</div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
            @if ($dispute->posted_1)
                @if(!$dispute->sent)
                    <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#updateSentModal">
                        <i class="bi bi-clock-history"></i> <span class="d-none d-sm-inline">Update Sent Info</span><span class="d-inline d-sm-none">Sent Info</span>
                    </button>
                    <a href="{{ route('disputes.downloadPdf', $dispute->id) }}" class="btn btn-dark btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Download PDF</span><span class="d-inline d-sm-none">PDF</span>
                    </a>
                @endif

                @php
                    $eligibleForFollowUp = $dispute->sent_date && $dispute->sent_date->addDays(14)->lte(now());
                @endphp

                @if ($eligibleForFollowUp)
                    <form action="{{ route('disputes.generateFollowUp', $dispute->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info text-white btn-sm d-flex align-items-center gap-2">
                            <i class="bi bi-envelope-paper"></i> <span class="d-none d-sm-inline">Generate Follow-Up</span><span class="d-inline d-sm-none">Follow-Up</span>
                        </button>
                    </form>
                @endif
            @endif

            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#guidelinesModal">
                <i class="bi bi-question-circle"></i> Help
            </button>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column: Info Sidebar --}}
        <div class="col-lg-4">
            
            {{-- Status Card --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary"></i> Dispute Status
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <span class="text-muted">Current Status</span>
                        @if ($dispute->posted_1)
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Posted
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                <i class="bi bi-hourglass-split me-1"></i> Pending
                            </span>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Subject</label>
                        {{-- Added ID for Live Update --}}
                        <div class="fw-medium text-dark" id="displaySubject">
                             {{ \Illuminate\Support\Str::of($dispute->letter_content)->after('Subject:')->before("\n")->trim() }}
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Created</label>
                            <div class="fw-medium">{{ $dispute->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1">Creditor</label>
                            <div class="fw-medium">{{ $dispute->creditor_name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    {{-- Actions Block --}}
                    <div class="bg-light rounded p-3">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Available Actions</label>
                        <form action="{{ route('disputes.togglePost', $dispute->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            @if(!$dispute->sent)
                                @if ($dispute->posted_1)
                                    <button class="btn btn-warning w-100 btn-sm d-flex align-items-center justify-content-center gap-2" type="submit">
                                        <i class="bi bi-pencil-square"></i> Edit (Unpost)
                                    </button>
                                @else
                                    <button class="btn btn-success w-100 btn-sm d-flex align-items-center justify-content-center gap-2 mb-2" type="submit">
                                        <i class="bi bi-check-lg"></i> Mark as Final
                                    </button>
                                    @if(auth()->user()->role === 'admin' || auth()->user()->id === $dispute->user_id)
                                        <button type="button" class="btn btn-outline-primary w-100 btn-sm d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#editLetterModal">
                                            <i class="bi bi-pencil"></i> Edit Content
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- Timeline Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i> Timeline
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="timeline position-relative ps-3">
                        {{-- Timeline items --}}
                        <div class="timeline-item pb-4 position-relative border-start ps-4">
                            <span class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle border border-white" style="width: 12px; height: 12px; margin-left: -1px;"></span>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">Created</div>
                            <div class="text-muted small">{{ $dispute->created_at->format('M d, Y') }}</div>
                        </div>

                        <div class="timeline-item pb-4 position-relative border-start ps-4">
                             <span class="position-absolute top-0 start-0 translate-middle {{ $dispute->posted_1 ? 'bg-primary' : 'bg-secondary' }} rounded-circle border border-white" style="width: 12px; height: 12px; margin-left: -1px;"></span>
                            <div class="fw-bold {{ $dispute->posted_1 ? 'text-dark' : 'text-muted' }}" style="font-size: 0.9rem;">Finalized</div>
                             @if($dispute->posted_1)
                                <div class="text-success small">Ready</div>
                            @else
                                <div class="text-muted small">Pending</div>
                            @endif
                        </div>

                        <div class="timeline-item pb-4 position-relative border-start ps-4">
                            <span class="position-absolute top-0 start-0 translate-middle {{ $dispute->sent ? 'bg-primary' : 'bg-secondary' }} rounded-circle border border-white" style="width: 12px; height: 12px; margin-left: -1px;"></span>
                            <div class="fw-bold {{ $dispute->sent ? 'text-dark' : 'text-muted' }}" style="font-size: 0.9rem;">Mailed</div>
                            @if($dispute->sent)
                                <div class="text-success small">{{ $dispute->sent_date ? $dispute->sent_date->format('M d, Y') : 'Sent' }}</div>
                            @else
                                <div class="text-muted small">Not yet</div>
                            @endif
                        </div>

                        <div class="timeline-item position-relative ps-4 text-secondary">
                             <span class="position-absolute top-0 start-0 translate-middle {{ (isset($eligibleForFollowUp) && $eligibleForFollowUp) ? 'bg-primary' : 'bg-secondary' }} rounded-circle border border-white" style="width: 12px; height: 12px; margin-left: -1px;"></span>
                            <div class="fw-bold {{ (isset($eligibleForFollowUp) && $eligibleForFollowUp) ? 'text-dark' : 'text-muted' }}" style="font-size: 0.9rem;">Follow-Up</div>
                             @if($dispute->sent && $dispute->sent_date)
                                <div class="text-muted small">
                                    {{ $dispute->sent_date->copy()->addDays(15)->format('M d, Y') }}
                                </div>
                            @else
                                <div class="text-muted small">wait 15 days</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Preview --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-primary"></i> Letter Preview
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                         <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard()" title="Copy Text">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <span class="badge bg-light text-dark border">A4 Portrait</span>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center" style="min-height: 500px; overflow-y: auto;">
                    
                    {{-- Form for Saving --}}
                    <form id="inlineEditForm" method="POST" action="{{ route('disputes.updateLetter', $dispute->id) }}" style="width: 100%;">
                        @csrf
                        @method('PATCH')
                        
                        {{-- VIEW MODE: The nice looking div --}}
                        {{-- Responsive Padding: p-3 on mobile, p-md-5 on desktop --}}
                        {{-- Added ID for Live Update --}}
                        <div id="letterPreviewContainer" class="letter-preview shadow bg-white p-3 p-md-5 mx-auto my-3 my-md-4" style="width: 100%; max-width: 800px; min-height: 600px; font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; color: #000;">
                            {!! nl2br(e($dispute->letter_content)) !!}
                        </div>

                        {{-- EDIT MODE: The textarea (Hidden by default) --}}
                        <textarea name="letter_content" id="letterContentInput" class="letter-preview shadow mx-auto my-3 my-md-4 p-3 p-md-5 d-none" rows="25" style="width: 100%; max-width: 800px;">{{ $dispute->letter_content }}</textarea>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
{{-- Edit Letter Modal --}}
<div class="modal fade" id="editLetterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form method="POST" action="{{ route('disputes.updateLetter', $dispute->id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Edit Letter Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Added ID for script targeting --}}
                <textarea name="letter_content" id="letterContentInput" class="form-control font-monospace @error('letter_content') is-invalid @enderror" rows="20" required>{{ old('letter_content', $dispute->letter_content) }}</textarea>
                <div class="form-text text-muted small mt-2"><i class="bi bi-info-circle"></i> content must be at least 20 characters long to save.</div>
                @error('letter_content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
  </div>
</div>

{{-- Update Sent Modal --}}
<div class="modal fade" id="updateSentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('disputes.updateSent', $dispute->id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📤 Update Sent Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-check mb-3 p-3 border rounded bg-light">
                    <input class="form-check-input" type="checkbox" name="sent" id="sentCheckbox" value="1" {{ $dispute->sent ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="sentCheckbox">
                        I have mailed this letter
                    </label>
                    <div class="text-muted small mt-1">Check this box only after you have physically mailed the letter to the credit bureau.</div>
                </div>

                <div class="mb-3">
                    <label for="sentDate" class="form-label fw-bold">Date Mailed</label>
                    <input type="date" name="sent_date" id="sentDate" class="form-control" value="{{ old('sent_date', $dispute->sent_date ? $dispute->sent_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Status</button>
            </div>
        </div>
    </form>
  </div>
</div>

{{-- Guidelines Modal --}}
<div class="modal fade" id="guidelinesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">📘 Guidelines for Dispute Processing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-3 mb-4">
            <div class="display-4 text-primary"><i class="bi bi-1-circle"></i></div>
            <div>
                <h5>Review & Finalize</h5>
                <p class="text-muted">Review the generated letter content on the right. Make any necessary edits to ensure accuracy. When ready, click <strong>Mark as Final</strong>.</p>
            </div>
        </div>
        <div class="d-flex gap-3 mb-4">
            <div class="display-4 text-primary"><i class="bi bi-2-circle"></i></div>
            <div>
                <h5>Download & Mail</h5>
                <p class="text-muted">Download the PDF version of your letter. Print it out and mail it to the referenced credit bureau.</p>
            </div>
        </div>
        <div class="d-flex gap-3 mb-4">
            <div class="display-4 text-primary"><i class="bi bi-3-circle"></i></div>
            <div>
                <h5>Track Status</h5>
                <p class="text-muted">Come back here and click <strong>Update Sent Info</strong> to record the mailing date. This starts your 15-day timer for follow-ups.</p>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Got it</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    function copyToClipboard() {
        const letterContent = document.getElementById('letterPreviewContainer').innerText;
        navigator.clipboard.writeText(letterContent).then(() => {
            alert('Letter content copied to clipboard!');
        });
    }

    // Live Preview Logic
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('letterContentInput'); // Textarea in modal
        const preview = document.getElementById('letterPreviewContainer'); // Div on page
        const subjectDisplay = document.getElementById('displaySubject'); // Subject sidebar div

        if (input) {
            input.addEventListener('input', function() {
                // 1. Update Letter Preview
                if (preview) {
                    preview.innerHTML = this.value.replace(/\n/g, '<br>');
                }

                // 2. Update Subject Logic (Strict matching of PHP logic)
                // Logic: after "Subject:" -> before next newline -> trim
                if (subjectDisplay) {
                    const content = this.value;
                    const splitText = content.split('Subject:');
                    
                    if (splitText.length > 1) {
                        // We have a "Subject:"
                        const afterSubject = splitText[1];
                        // Get text before the next new line
                        const subjectLine = afterSubject.split('\n')[0];
                        // Trim whitespace
                        subjectDisplay.textContent = subjectLine.trim();
                    } else {
                        // Fallback if user deletes "Subject:"
                        subjectDisplay.textContent = ''; 
                    }
                }
            });
        }
    });
</script>
@endpush
