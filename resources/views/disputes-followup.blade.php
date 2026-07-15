@extends('layouts.app')

@section('title', 'Follow-Up Letter')

@section('content')

<style>
    @media (min-width: 1200px) {
        .container {
            max-width: 1140px; /* slightly less than default xl */
        }
    }
</style>

@php
    use Illuminate\Support\Str;

    $subject = trim(Str::of($dispute->letter_content)->after('Subject:')->before("\n"));
@endphp

<div class="container mt-5">
    <h4 class="mb-4 d-flex justify-content-between align-items-center">
        📬 Follow-Up Letter

        @if ($dispute->letter_content_2)
            <a href="{{ route('disputes.downloadFollowUpPdf', $dispute->id) }}" class="btn btn-outline-secondary btn-sm">
                📄 Download Follow-Up PDF
            </a>
        @endif
    </h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted mb-2">
                <strong>From Letter 1:</strong>
                <a href="{{ url('my-disputes/' . $dispute->id) }}">
                    {{ $subject }}
                </a>
            </p>

            <h5 class="mt-4">✉️ Generated Follow-Up Letter</h5>
            <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $followUpLetter }}</pre>
        </div>
    </div>
</div>
@endsection
