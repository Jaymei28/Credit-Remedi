@extends('layouts.blank')

@section('title', 'Join Waitlist')

@section('content')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
    }

    .register-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .register-card {
        background-color: #fff;
        padding: 2rem 2.5rem;
        border-radius: 10px;
        max-width: 900px;
        width: 100%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .logo-img {
        height: 80px;
        object-fit: contain;
        margin-bottom: 1rem;
    }

    .section-title {
        font-weight: 600;
        font-size: 0.85rem;
        color: #343a40;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }


    .form-label {
        font-size: 0.8rem;
        margin-bottom: 0.2rem;
    }

    .form-control {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }

    button#submit-button {
        font-size: 0.9rem;
        padding: 0.6rem;
        transition: background 0.2s ease;
    }

    .hidden-field {
        display: none;
    }
</style>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="text-center">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="logo-img">
                <h2 class="mb-4 text-center">Join the Waitlist</h2>
            </div>
            
            @if(session('status'))
                <div class="alert alert-success text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger small">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('waitlist.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="challenge">1. What’s your biggest credit challenge right now?</label>
                    <select name="challenge" id="challenge" class="form-select" required>
                        <option value="" disabled selected>Select your challenge</option>
                        <option value="Removing collections or charge-offs">Removing collections or charge-offs</option>
                        <option value="Getting late payments removed">Getting late payments removed</option>
                        <option value="Increasing my score for funding or housing">Increasing my score for funding or housing</option>
                        <option value="Learning how to repair my credit on my own">Learning how to repair my credit on my own</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="usage">2. How do you plan to use Credit Remedi AI?</label>
                    <select name="usage" id="usage" class="form-select" required>
                        <option value="" disabled selected>Select how you’ll use it</option>
                        <option value="To dispute faster and more effectively">To dispute faster and more effectively</option>
                        <option value="To maintain and protect my credit">To maintain and protect my credit</option>
                        <option value="For business or client credit repair">For business or client credit repair</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="timeline">3. How soon do you want to see results?</label>
                    <select name="timeline" id="timeline" class="form-select" required>
                        <option value="" disabled selected>Select your timeline</option>
                        <option value="Within 45 days">Within 45 days</option>
                        <option value="1–3 months">1–3 months</option>
                        <option value="3–6 months">3–6 months</option>
                        <option value="No rush, I’m here to learn and prepare">No rush, I’m here to learn and prepare</option>
                    </select>
                </div>

                <input type="hidden" name="referrer_code" value="{{ $ref }}">

                <button type="submit" class="btn btn-primary w-100">Join Waitlist</button>
            </form>

        </div>
    </div>
</div>
@endsection
