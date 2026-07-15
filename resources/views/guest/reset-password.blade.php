@extends('layouts.blank')

@section('title', 'Reset Password')

@section('content')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
    }

    .reset-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .reset-card {
        background-color: #fff;
        padding: 2rem;
        border-radius: 10px;
        max-width: 450px;
        width: 100%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .logo-img {
        height: 80px;
        object-fit: contain;
        margin-bottom: 0.75rem;
    }

    .form-label {
        font-size: 0.875rem;
    }
</style>

<div class="reset-wrapper">
    <div class="reset-card">
        <div class="text-center mb-3">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="logo-img">
            <h2 class="h6 fw-bold mb-1">Reset Password</h2>
            <p class="text-muted small">Enter a new password to regain access to your account.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ request()->email }}">

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Reset Password</button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="small text-decoration-none text-primary">Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
