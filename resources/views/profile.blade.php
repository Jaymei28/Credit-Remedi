@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .profile-container {
        font-family: 'Inter', sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    /* Profile Header Card */
    .profile-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        animation: fadeInDown 0.6s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: #667eea;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-align: center;
    }

    .profile-email {
        font-size: 1rem;
        opacity: 0.9;
        text-align: center;
    }

    /* Info Cards Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease;
        animation-fill-mode: both;
    }

    .info-card:nth-child(1) { animation-delay: 0.1s; }
    .info-card:nth-child(2) { animation-delay: 0.2s; }
    .info-card:nth-child(3) { animation-delay: 0.3s; }
    .info-card:nth-child(4) { animation-delay: 0.4s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .info-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .info-card-icon.address { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    .info-card-icon.contact { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    .info-card-icon.location { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; }
    .info-card-icon.security { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }

    .info-card-label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .info-card-value {
        font-size: 1.1rem;
        color: #1f2937;
        font-weight: 600;
    }

    /* Action Buttons */
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        animation: fadeInUp 0.6s ease 0.5s;
        animation-fill-mode: both;
    }

    .action-btn {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .action-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .action-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        color: white;
    }

    .action-btn-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .action-btn-secondary:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    /* Modern Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        border-bottom: 2px solid #f3f4f6;
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px 20px 0 0;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.5rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: 2px solid #f3f4f6;
        padding: 1.5rem 2rem;
    }

    .form-group-modern {
        margin-bottom: 1.5rem;
    }

    .form-label-modern {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input-modern {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .form-input-modern:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-input-modern:read-only {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .modal-btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }

    .modal-btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .modal-btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }

    .modal-btn-cancel:hover {
        background: #e5e7eb;
        color: #374151;
    }

    /* Alerts */
    .alert-modern {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success-modern {
        background: #d1fae5;
        color: #065f46;
    }

    .alert-danger-modern {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Dark mode support */
    [data-theme="dark"] .info-card {
        background: #1f2937;
    }

    [data-theme="dark"] .info-card-label {
        color: #9ca3af;
    }

    [data-theme="dark"] .info-card-value {
        color: #f3f4f6;
    }

    [data-theme="dark"] .modal-content {
        background: #1f2937;
    }

    [data-theme="dark"] .modal-body {
        color: #f3f4f6;
    }

    [data-theme="dark"] .form-input-modern {
        background: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    [data-theme="dark"] .form-input-modern:focus {
        background: #1f2937;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-header-card {
            padding: 2rem 1.5rem;
        }

        .profile-name {
            font-size: 1.75rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-container">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert-modern alert-success-modern">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-modern alert-danger-modern">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-modern alert-danger-modern">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    @php
        $user = auth()->user();
        $initials = strtoupper(substr($user->name, 0, 1));
    @endphp

    {{-- Profile Header --}}
    <div class="profile-header-card">
        <div class="profile-avatar">{{ $initials }}</div>
        <h1 class="profile-name">{{ $user->name }}</h1>
        <p class="profile-email"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
    </div>

    {{-- Info Cards Grid --}}
    <div class="info-grid">
        {{-- Address Card --}}
        <div class="info-card">
            <div class="info-card-icon address">
                <i class="bi bi-house-door"></i>
            </div>
            <div class="info-card-label">Street Address</div>
            <div class="info-card-value">{{ $user->address ?? 'Not provided' }}</div>
        </div>

        {{-- Location Card --}}
        <div class="info-card">
            <div class="info-card-icon location">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div class="info-card-label">Location</div>
            <div class="info-card-value">
                {{ $user->city ?? 'N/A' }}, {{ $user->state ?? 'N/A' }} {{ $user->zipcode ?? '' }}
            </div>
        </div>

        {{-- Contact Card --}}
        <div class="info-card">
            <div class="info-card-icon contact">
                <i class="bi bi-telephone"></i>
            </div>
            <div class="info-card-label">Contact Number</div>
            <div class="info-card-value">{{ $user->contact_number ?? 'Not provided' }}</div>
        </div>

        {{-- Security Card --}}
        <div class="info-card">
            <div class="info-card-icon security">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="info-card-label">SSN (Last 4 Digits)</div>
            <div class="info-card-value">****-****-{{ $user->ssn_last4 ?? 'N/A' }}</div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-buttons">
        <button class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#editDetailsModal">
            <i class="bi bi-pencil-square"></i> Edit Details
        </button>
        <button class="action-btn action-btn-secondary" data-bs-toggle="modal" data-bs-target="#passwordModal">
            <i class="bi bi-key"></i> Change Password
        </button>
    </div>

    {{-- Edit Details Modal --}}
    <div class="modal fade" id="editDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Update Your Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Full Name</label>
                            <input type="text" name="name" class="form-input-modern" value="{{ old('name', $user->name) }}" readonly>
                            <small class="text-muted">Name cannot be changed</small>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Street Address</label>
                            <input type="text" name="address" class="form-input-modern" value="{{ old('address', $user->address) }}" placeholder="Enter your street address">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">City</label>
                                    <input type="text" name="city" class="form-input-modern" value="{{ old('city', $user->city) }}" placeholder="City">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">State</label>
                                    <input type="text" name="state" class="form-input-modern" value="{{ old('state', $user->state) }}" placeholder="State" maxlength="2">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Zip Code</label>
                                    <input type="text" name="zipcode" class="form-input-modern" value="{{ old('zipcode', $user->zipcode) }}" placeholder="Zip">
                                </div>
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Contact Number</label>
                            <input type="text" name="contact_number" class="form-input-modern" value="{{ old('contact_number', $user->contact_number) }}" placeholder="(555) 123-4567">
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">SSN (Last 4 Digits)</label>
                            <input type="text" name="ssn_last4" class="form-input-modern" value="{{ old('ssn_last4', $user->ssn_last4) }}" maxlength="4" placeholder="1234">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-btn-save">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password Modal --}}
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-key me-2"></i>Change Your Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-modern">
                            <label class="form-label-modern">New Password</label>
                            <input type="password" name="password" class="form-input-modern" placeholder="Enter new password" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-input-modern" placeholder="Confirm new password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-btn-save">
                            <i class="bi bi-check-circle me-1"></i>Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-close alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-modern');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
</script>
@endpush
