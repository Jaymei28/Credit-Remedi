@extends('layouts.blank')

@section('title', 'Login - Credit Remedi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    .login-container {
        min-height: 100vh;
        display: flex;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }

    /* Animated Background Elements */
    .bg-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }

    .shape {
        position: absolute;
        opacity: 0.1;
        animation: float 20s infinite ease-in-out;
    }

    .shape-1 {
        width: 300px;
        height: 300px;
        background: white;
        border-radius: 50%;
        top: -100px;
        left: -100px;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 200px;
        height: 200px;
        background: white;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        bottom: -50px;
        right: 10%;
        animation-delay: 2s;
    }

    .shape-3 {
        width: 150px;
        height: 150px;
        background: white;
        border-radius: 50%;
        top: 50%;
        right: -75px;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-30px) rotate(180deg);
        }
    }

    /* Left Side - Branding */
    .login-left {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        position: relative;
        z-index: 1;
    }

    .branding-content {
        max-width: 500px;
        color: white;
        animation: slideInLeft 0.8s ease;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .logo-container {
        margin-bottom: 2.5rem;
    }

    .logo-img {
        height: 80px;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .branding-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .branding-subtitle {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }

    .feature-item:nth-child(1) { animation-delay: 0.2s; }
    .feature-item:nth-child(2) { animation-delay: 0.4s; }
    .feature-item:nth-child(3) { animation-delay: 0.6s; }

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

    .feature-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        backdrop-filter: blur(10px);
    }

    .feature-text {
        font-size: 1rem;
        opacity: 0.95;
    }

    /* Right Side - Login Form */
    .login-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        position: relative;
        z-index: 1;
    }

    .login-card {
        background: white;
        border-radius: 24px;
        padding: 3rem;
        max-width: 480px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideInRight 0.8s ease;
        position: relative;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .login-subtitle {
        color: #6b7280;
        font-size: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        font-size: 1.2rem;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: #667eea;
    }

    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .checkbox-wrapper input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .checkbox-label {
        font-size: 0.9rem;
        color: #6b7280;
        cursor: pointer;
    }

    .forgot-link {
        font-size: 0.9rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .forgot-link:hover {
        color: #764ba2;
    }

    .login-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }

    .divider::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 100%;
        height: 1px;
        background: #e5e7eb;
    }

    .divider-text {
        position: relative;
        background: white;
        padding: 0 1rem;
        color: #9ca3af;
        font-size: 0.9rem;
    }

    .register-link {
        text-align: center;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .register-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .register-link a:hover {
        color: #764ba2;
    }

    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
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

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
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

    .contact-support-bottom {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .support-link-bottom {
        color: #6b7280;
        font-size: 0.95rem;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .support-link-bottom span {
        color: #667eea;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .support-link-bottom:hover span {
        color: #764ba2;
    }

    [data-theme="dark"] .contact-support-bottom {
        border-top-color: #4b5563;
    }

    [data-theme="dark"] .support-link-bottom {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .login-left {
            display: none;
        }

        .login-right {
            flex: 1;
        }
    }

    @media (max-width: 576px) {
        .login-card {
            padding: 2rem 1.5rem;
        }

        .login-title {
            font-size: 1.75rem;
        }

        .branding-title {
            font-size: 2rem;
        }
    }

    /* Dark mode support */
    [data-theme="dark"] .login-card {
        background: #1f2937;
    }

    [data-theme="dark"] .login-title {
        color: #f3f4f6;
    }

    [data-theme="dark"] .login-subtitle,
    [data-theme="dark"] .checkbox-label,
    [data-theme="dark"] .register-link {
        color: #9ca3af;
    }

    [data-theme="dark"] .form-label {
        color: #e5e7eb;
    }

    [data-theme="dark"] .form-input {
        background: #374151;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    [data-theme="dark"] .form-input:focus {
        background: #1f2937;
        border-color: #667eea;
    }

    [data-theme="dark"] .divider::before {
        background: #4b5563;
    }

    [data-theme="dark"] .divider-text {
        background: #1f2937;
    }
</style>

<div class="login-container">
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Left Side - Branding -->
    <div class="login-left">
        <div class="branding-content">
            <div class="logo-container">
                <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi" class="logo-img">
            </div>
            <h1 class="branding-title">Meet Ally powered by Credit Remedi</h1>
            <p class="branding-subtitle">
                Your AI-powered credit repair journey continues here. Let's fix your credit together.
            </p>
            <ul class="feature-list">
                <li class="feature-item">
                    <div class="feature-icon" style="padding: 2px;">
                        <img src="{{ asset('images/AllyAI.png') }}" alt="Ally" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
                    </div>
                    <div class="feature-text">AI-Powered Credit Analysis</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">📄</div>
                    <div class="feature-text">Automated Dispute Letters</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">📈</div>
                    <div class="feature-text">Track Your Progress</div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="login-right">
        <div class="login-card">
            @auth
                <div class="login-header">
                    <h2 class="login-title">You're Already Logged In!</h2>
                    <p class="login-subtitle">Ready to continue your credit repair journey?</p>
                </div>
                <a href="{{ route('dashboard') }}" class="login-btn" style="display: block; text-align: center; text-decoration: none;">
                    Go to Dashboard
                </a>
            @endauth

            @guest
                <div class="login-header">
                    <h2 class="login-title">Sign In</h2>
                    <p class="login-subtitle">Welcome back! Please enter your details.</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            class="form-input" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            required 
                            autofocus
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                class="form-input" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>


                    <div class="remember-forgot">
                        <div class="checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                            >
                            <label for="remember" class="checkbox-label">Remember me</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">Sign In</button>

                    <div class="divider">
                        <span class="divider-text">or</span>
                    </div>

                    <div class="register-link">
                        Don't have an account? <a href="{{ route('plans', ['ref' => request('ref')]) }}">Sign up</a>
                    </div>

                    <!-- Setup Support Contact Link below everything -->
                    <div class="contact-support-bottom">
                        <a href="mailto:help@creditremedi.com?subject=Need Help Logging In - Credit Remedi" class="support-link-bottom">
                            <i class="bi bi-info-circle"></i> Having trouble logging in? <span style="text-decoration: underline;">Contact Support</span>
                        </a>
                    </div>
                </form>
            @endguest
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }
</script>
@endsection
