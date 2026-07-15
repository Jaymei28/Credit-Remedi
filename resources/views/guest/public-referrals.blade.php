@extends('layouts.blank')

@section('title', 'Referral Info')

@section('content')
<style>
    @media (min-width: 768px) {
        .referral-container {
            max-width: 720px;
        }
    }

    @media (min-width: 992px) {
        .referral-container {
            max-width: 620px;
        }
    }

    .referral-container {
        margin: 0 auto;
        text-align: center;
    }

    .logo-img {
        max-height: 80px;
        margin-bottom: 1.5rem;
    }
</style>

<div class="container py-5 referral-container">

    <!-- Logo -->
    <img src="{{ asset('logo.png') }}" alt="Logo" class="logo-img">

    <h2 class="mb-4">Referral Details</h2>

    <div class="card mb-4 text-start">
        <div class="card-body">
            <p><strong>Referral Code:</strong> {{ $user->referral_code }}</p>
            <p><strong>Referred By:</strong> {{ $user->name }}</p>
            <p><strong>Total Referrals:</strong> {{ $referrals->count() }}</p>
            <p><strong>Referral Link:</strong> 
                <code>{{ url('/waitlist?ref=' . $user->referral_code) }}</code>
            </p>
        </div>
    </div>

    <div class="card text-start">
        <div class="card-header">People Referred</div>
        <div class="card-body p-0">
            @if ($referrals->isEmpty())
                <p class="text-center text-muted my-3">No referrals yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $ref)
                                <tr>
                                    <td>{{ $ref->name }}</td>
                                    <td>{{ $ref->email }}</td>
                                    <td>{{ $ref->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
