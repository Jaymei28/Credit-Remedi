@extends('layouts.blank')

@section('title', 'Forgot Password - Credit Remedi')

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

    .forgot-container {
        min-height: 100vh;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
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
        right: -100px;
    }

    .shape-2 {
        width: 300px;
        height: 300px;
        background: white;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        bottom: -100px;
        left: -50px;
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

    .forgot-card {
        background: white;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        max-width: 480px;
        width: 100%;
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

    .forgot-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo-img {
        height: 150px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .forgot-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937 !important;
        margin-bottom: 0.5rem;
    }

    .forgot-subtitle {
        color: #6b7280 !important;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151 !important;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
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

    .submit-button {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

    .back-link {
        text-align: center;
        margin-top: 1.5rem;
        color: #6b7280 !important;
    }

    .back-link a {
        color: #667eea !important;
        text-decoration: none;
        font-weight: 600;
    }

    .back-link a:hover {
        color: #764ba2 !important;
    }

    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46 !important;
        border: 1px solid #a7f3d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b !important;
        border: 1px solid #fecaca;
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .icon-wrapper i {
        font-size: 1.8rem;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .forgot-card {
            padding: 2rem 1.5rem;
        }

        .forgot-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="forgot-container">
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="forgot-card">
        <div class="forgot-header">
            <img src="{{ asset('android-chrome-512x512-removebg-preview.png') }}" alt="Credit Remedi" class="logo-img">
            
            <div class="icon-wrapper">
                <i class="bi bi-key-fill"></i>
            </div>
            
            <h1 class="forgot-title">Forgot Password?</h1>
            <p class="forgot-subtitle">No worries! Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-input" 
                       value="{{ old('email') }}"
                       placeholder="your.email@example.com"
                       required 
                       autofocus>
            </div>

            <button type="submit" class="submit-button">
                <i class="bi bi-send-fill me-2"></i>Send Reset Link
            </button>

            <div class="back-link">
                <a href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
