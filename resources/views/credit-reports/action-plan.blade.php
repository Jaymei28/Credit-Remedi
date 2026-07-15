@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Action Plan for Report #{{ $report->id }}</h2>

    <h4>Uploaded File:</h4>
    <p>{{ $report->original_filename }} (uploaded {{ $report->created_at->format('M d, Y') }})</p>

    <h4>Extracted Text Preview:</h4>
    <pre style="max-height:200px;overflow:auto;white-space:pre-wrap">
        {{ Str::limit($report->extracted_text, 2000) }}
    </pre>

    <h4>Generated Action Plan:</h4>
    <div style="white-space:pre-wrap; background:#f8f9fa; padding:1rem; border-radius:8px;">
        {{ $actionPlan }}
    </div>

    <a href="{{ route('credit-reports.show', $report->id) }}" class="btn btn-secondary mt-3">⬅ Back to Report</a>
</div>
@endsection
