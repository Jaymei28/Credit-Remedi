@extends('layouts.blank')

@section('title', 'Join Credit Remedi')

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

    .register-container {
        min-height: 100vh;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
    }

    /* Animated Background */
    .bg-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }

    .shape {
        position: absolute;
        opacity: 0.05;
        animation: float 20s infinite ease-in-out;
    }

    .shape-1 {
        width: 400px;
        height: 400px;
        background: white;
        border-radius: 50%;
        top: -150px;
        left: -100px;
    }

    .shape-2 {
        width: 300px;
        height: 300px;
        background: white;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        bottom: -100px;
        right: -50px;
        animation-delay: 3s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-30px) rotate(180deg);
        }
    }

    .register-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        max-width: 1000px;
        margin: 0 auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease;
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

    .register-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .logo-img {
        height: 100px;
        width: 120px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .register-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937 !important;
        margin-bottom: 0.5rem;
    }

    .register-subtitle {
        color: #dc2626 !important;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .section-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .section-icon.personal {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .section-icon.login {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .section-icon.address {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .section-icon.payment {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937 !important;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151 !important;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .required {
        color: #dc2626 !important;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        color: #1f2937 !important;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-hint {
        font-size: 0.85rem;
        color: #6b7280 !important;
        margin-top: 0.25rem;
    }

    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .submit-button {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1.5rem;
    }

    .submit-button.starter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .submit-button.premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .submit-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .login-link {
        text-align: center;
        margin-top: 1.5rem;
        color: #6b7280 !important;
    }

    .login-link a {
        color: #667eea !important;
        text-decoration: none;
        font-weight: 600;
    }

    .login-link a:hover {
        color: #764ba2 !important;
    }

    .security-note {
        text-align: center;
        margin-top: 1rem;
        color: #9ca3af !important;
        font-size: 0.9rem;
    }

    .hidden-field {
        display: none;
    }

    #card-element {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    #card-element.StripeElement--focus {
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    #card-errors {
        color: #dc2626;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .register-card {
            padding: 2rem 1.5rem;
        }

        .register-title {
            font-size: 1.75rem;
        }

        .section-title {
            font-size: 1rem;
        }
    }
</style>

@php
    $plan = request('plan', 'starter');
    $planName = $plan === 'premium' ? 'Turbo Plan' : 'Standard Plan';
    $amount = $plan === 'premium' ? 69 : 49;
@endphp

<div class="register-container">
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="register-card">
        <div class="register-header">
            <img src="{{ asset('CreditRemedi.png') }}" alt="Credit Remedi" class="logo-img">
            <h1 class="register-title">Join Credit Remedi</h1>
            <p class="register-subtitle">* Please ensure all details are correct. This information will be used in all official letters.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" id="registration-form">
            @csrf

            <input type="text" name="website" class="hidden-field" autocomplete="off">

            <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-md-6">
                    <!-- Personal Info -->
                    <div class="section-header">
                        <div class="section-icon personal">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="section-title">PERSONAL INFO</div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-input" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number" class="form-label">Contact Number <span class="required">*</span></label>
                        <input type="text" class="form-input" name="contact_number" value="{{ old('contact_number') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="ssn_last4" class="form-label">Last 4 Digits of SSN <span class="required">*</span></label>
                        <input type="text" class="form-input" name="ssn_last4" value="{{ old('ssn_last4') }}" 
                            maxlength="4" pattern="\d{4}" placeholder="1234" required>
                        <div class="form-hint">For verification purposes only.</div>
                    </div>

                    <!-- Address Info -->
                    <div class="section-header">
                        <div class="section-icon address">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="section-title">ADDRESS INFO</div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">Street Address <span class="required">*</span></label>
                        <input type="text" class="form-input" name="address" value="{{ old('address') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city" class="form-label">City <span class="required">*</span></label>
                                <input type="text" class="form-input" name="city" value="{{ old('city') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="state" class="form-label">State <span class="required">*</span></label>
                                <input type="text" class="form-input" name="state" value="{{ old('state') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="zipcode" class="form-label">Zip Code <span class="required">*</span></label>
                                <input type="text" class="form-input" name="zipcode" value="{{ old('zipcode') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-md-6">
                    <!-- Login Info -->
                    <div class="section-header">
                        <div class="section-icon login">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div class="section-title">LOGIN INFO</div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-input" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <input type="password" class="form-input" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" class="form-input" name="password_confirmation" required>
                    </div>

                    <!-- Payment Info -->
                    <div class="section-header">
                        <div class="section-icon payment">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div class="section-title">PAYMENT INFO</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment via PayPal</label>
                        <div id="paypal-button-container"></div>
                        <div id="paypal-errors" class="text-danger small mt-2"></div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="paypalOrderId" id="paypalOrderId">

            <input type="hidden" name="selected_plan" value="{{ $plan }}">

            {{-- PayPal button will be rendered here --}}

            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}">Login here</a>
            </div>

            <div class="security-note">
                🔒 Secured by PayPal. Monthly subscription. Cancel anytime.
            </div>
            <!-- Temporary Debug Label -->
            <div style="font-size: 10px; color: #ccc; margin-top: 10px;">
                SERVER MODE: {{ config('services.paypal.mode') }} | CLIENT: {{ substr(config('services.paypal.client_id'), 0, 10) }}...
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script 
    src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&vault=true&intent=subscription" 
    data-sdk-integration-source="button-factory">
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('registration-form');
        const paypalOrderIdInput = document.getElementById('paypalOrderId');
        const errorDiv = document.getElementById('paypal-errors');

        // Choose the correct Plan ID based on user selection
        const planId = "{{ $plan === 'premium' ? config('services.paypal.premium_plan_id') : config('services.paypal.starter_plan_id') }}";

        paypal.Buttons({
            style: {
                shape: 'rect',
                color: 'gold',
                layout: 'vertical',
                label: 'subscribe'
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    'plan_id': planId // Pass the specific Plan ID here
                });
            },
            onApprove: function(data, actions) {
                // Set the subscription ID to the hidden input
                paypalOrderIdInput.value = data.subscriptionID;
                
                Swal.fire({
                    title: 'Payment Approved',
                    text: 'Your subscription is active. We are now creating your account...',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    form.submit();
                });
            },
            onError: function(err) {
                errorDiv.textContent = "There was an error creating the subscription. Please try again.";
                console.error(err);
            }
        }).render('#paypal-button-container');

        // Prevent default form submission if clicking enter
        form.addEventListener('submit', (e) => {
            if (!paypalOrderIdInput.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Payment Required',
                    text: 'Please complete the PayPal payment to register.',
                    icon: 'warning'
                });
            }
        });
    });
</script>
@endpush
