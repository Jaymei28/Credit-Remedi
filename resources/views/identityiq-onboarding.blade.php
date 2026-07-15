@extends('layouts.blank')

@section('title', 'IdentityIQ Setup Required - Credit Remedi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .onboarding-container {
        min-height: 100vh;
        padding: 2rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .onboarding-card {
        background: white;
        border-radius: 24px;
        max-width: 700px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .onboarding-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: white;
    }

    .onboarding-header img {
        height: 60px;
        margin-bottom: 1rem;
    }

    .onboarding-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .onboarding-header p {
        font-size: 1rem;
        opacity: 0.95;
    }

    .onboarding-body {
        padding: 2.5rem 2rem;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e5e7eb;
        transition: all 0.3s ease;
    }

    .step-dot.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 32px;
        border-radius: 6px;
    }

    .info-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #3b82f6;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-banner-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .info-icon {
        width: 48px;
        height: 48px;
        background: #3b82f6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .info-banner h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e40af;
        margin: 0;
    }

    .info-banner p {
        color: #1e40af;
        margin-bottom: 0.75rem;
        line-height: 1.6;
    }

    .info-banner ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-banner li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: #1e40af;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .info-banner li::before {
        content: "✓";
        color: #10b981;
        font-weight: 700;
        flex-shrink: 0;
    }

    .action-section {
        background: #f9fafb;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .action-section h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .btn-action {
        width: 100%;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary-action {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        color: white;
    }

    .btn-secondary-action {
        background: white;
        color: #1f2937 !important;
        border: 2px solid #5b21b6;
    }

    .btn-secondary-action:hover {
        background: #5b21b6;
        color: white !important;
        transform: translateY(-2px);
    }

    .btn-secondary-action span {
        color: inherit !important;
        font-weight: 700 !important;
    }
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 2px solid #e5e7eb;
    }

    .divider span {
        padding: 0 1rem;
    }

    .help-text {
        text-align: center;
        color: #374151 !important;
        font-size: 0.875rem;
        margin-top: 1.5rem;
    }

    .help-text p {
        color: #374151 !important;
        margin: 0;
        font-weight: 500;
    }

    .help-text a {
        color: #5b21b6 !important;
        text-decoration: none;
        font-weight: 700;
    }

    .help-text a:hover {
        text-decoration: underline;
    }

    .upload-section {
        display: none;
    }

    .upload-section.active {
        display: block;
    }

    .file-upload-area {
        border: 3px dashed #d1d5db;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
    }

    .file-upload-area:hover {
        border-color: #667eea;
        background: #f9fafb;
    }

    .file-upload-area.dragover {
        border-color: #10b981;
        background: #d1fae5;
    }

    .upload-icon {
        font-size: 3rem;
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    .file-upload-area p {
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .file-upload-area .file-types {
        font-size: 0.875rem;
        color: #9ca3af;
    }

    .selected-file {
        display: none;
        background: #d1fae5;
        border: 2px solid #10b981;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
        align-items: center;
        gap: 0.75rem;
    }

    .selected-file.active {
        display: flex;
    }

    .selected-file-icon {
        width: 40px;
        height: 40px;
        background: #10b981;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .selected-file-info {
        flex: 1;
    }

    .selected-file-name {
        font-weight: 600;
        color: #065f46;
        margin-bottom: 0.25rem;
    }

    .selected-file-size {
        font-size: 0.875rem;
        color: #059669;
    }

    .remove-file {
        background: none;
        border: none;
        color: #dc2626;
        cursor: pointer;
        font-size: 1.25rem;
        padding: 0.5rem;
    }

    @media (max-width: 768px) {
        .onboarding-header h1 {
            font-size: 1.5rem;
        }

        .onboarding-body {
            padding: 2rem 1.5rem;
        }

        .action-buttons {
            gap: 0.75rem;
        }
    }
</style>

<div class="onboarding-container">
    <div class="onboarding-card">
        <div class="onboarding-header">
            <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi">
            <h1>🎯 Complete Your Setup</h1>
            <p>Let's get you ready to track your credit repair progress</p>
        </div>

        <div class="onboarding-body">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-dot active" id="step-dot-1"></div>
                <div class="step-dot" id="step-dot-2"></div>
            </div>

            <!-- Step 1: IdentityIQ Enrollment -->
            <div id="step-1" class="enrollment-section">
                <div class="info-banner">
                    <div class="info-banner-header">
                        <div class="info-icon">📊</div>
                        <h3>IdentityIQ Credit Monitoring Required</h3>
                    </div>
                    <p><strong>Why is this required?</strong></p>
                    <p>To provide accurate dispute tracking and credit score monitoring, we need you to enroll in IdentityIQ credit monitoring. This allows our system to:</p>
                    <ul>
                        <li>Track deletions and updates from credit bureaus in real-time</li>
                        <li>Monitor your credit score changes monthly</li>
                        <li>Generate accurate dispute letters based on your current report</li>
                        <li>Measure your credit repair progress over time</li>
                        <li>Alert you to new negative items or changes</li>
                    </ul>
                </div>

                <div class="action-section">
                    <h4>Step 1: Enroll in IdentityIQ</h4>
                    <div class="action-buttons">
                        <a href="https://enroll2.identityiq.com/?plancode=PLAN6X&offercode=431291AY&cart=true&_gl=1*18d81pa*_gcl_au*ODA1NzY2NTE1LjE3NjU4NjMyMDI." target="_blank" class="btn-action btn-primary-action" id="enroll-btn">
                            <span>🔗</span>
                            <span>Enroll in IdentityIQ Now</span>
                        </a>
                        
                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <form action="{{ route('identityiq.confirm-existing') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-action btn-secondary-action w-100">
                                <span>✓</span>
                                <span>I Already Have IdentityIQ</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="help-text">
                    <p>Need help? <a href="mailto:support@creditremedi.com">Contact Support</a></p>
                </div>
            </div>

            <!-- Step 2: Upload Initial Report (Hidden initially) -->
            <div id="step-2" class="upload-section">
                <div class="info-banner">
                    <div class="info-banner-header">
                        <div class="info-icon">📄</div>
                        <h3>Upload Your First Credit Report</h3>
                    </div>
                    <p><strong>This is your baseline report.</strong></p>
                    <p>Upload your IdentityIQ credit report so we can:</p>
                    <ul>
                        <li>Identify negative items to dispute</li>
                        <li>Generate personalized dispute letters</li>
                        <li>Track your progress over time</li>
                        <li>Provide accurate credit score analysis</li>
                    </ul>
                </div>

                <div class="action-section">
                    <h4>Step 2: Upload Your IdentityIQ Report</h4>
                    
                    <form action="{{ route('identityiq.upload-initial-report') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                        @csrf
                        <div class="file-upload-area" id="drop-area">
                            <div class="upload-icon">📁</div>
                            <p><strong>Drag and drop your credit report here</strong></p>
                            <p>or click to browse</p>
                            <p class="file-types">Supported: HTML, PDF, DOC, DOCX, JPG, PNG (Max 10MB)</p>
                            <input type="file" name="credit_report" id="file-input" accept=".html,.htm,.pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;" required>
                        </div>

                        <div class="selected-file" id="selected-file">
                            <div class="selected-file-icon">📄</div>
                            <div class="selected-file-info">
                                <div class="selected-file-name" id="file-name"></div>
                                <div class="selected-file-size" id="file-size"></div>
                            </div>
                            <button type="button" class="remove-file" id="remove-file">✕</button>
                        </div>

                        <div class="action-buttons" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn-action btn-primary-action" id="upload-btn" disabled>
                                <span>📤</span>
                                <span>Upload Report & Complete Setup</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="help-text">
                    <p>Can't find your report? <a href="mailto:support@creditremedi.com">Get Help</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Check if user came back from IdentityIQ enrollment
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('enrolled') === 'true') {
        showStep2();
    }

    function showStep2() {
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').classList.add('active');
        document.getElementById('step-dot-1').classList.remove('active');
        document.getElementById('step-dot-2').classList.add('active');
    }

    // File upload handling
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const selectedFile = document.getElementById('selected-file');
    const uploadBtn = document.getElementById('upload-btn');
    const removeFileBtn = document.getElementById('remove-file');

    // Click to browse
    dropArea.addEventListener('click', () => fileInput.click());

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when dragging over
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
    });

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFiles(files);
    }

    // Handle file selection
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            const fileName = file.name;
            const fileSize = formatFileSize(file.size);

            document.getElementById('file-name').textContent = fileName;
            document.getElementById('file-size').textContent = fileSize;
            selectedFile.classList.add('active');
            uploadBtn.disabled = false;
        }
    }

    // Remove file
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        selectedFile.classList.remove('active');
        uploadBtn.disabled = true;
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
</script>
@endsection
