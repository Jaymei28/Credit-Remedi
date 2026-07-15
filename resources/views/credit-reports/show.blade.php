<h2>Credit Report: {{ $report->original_filename }}</h2>

<p><strong>Uploaded:</strong> {{ $report->created_at->format('M d, Y') }}</p>

<h3>Extracted Text</h3>
<pre style="max-height:200px;overflow:auto;white-space:pre-wrap">{{ $report->extracted_text }}</pre>

<h3>Action Plan</h3>
@if($report->action_plan)
    <pre style="white-space:pre-wrap">{{ $report->action_plan }}</pre>
@else
    <form action="{{ route('credit-reports.actionPlan', $report->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Generate Action Plan</button>
    </form>
@endif


<form action="{{ route('credit-reports.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="pdf_file">Upload Credit Report PDF:</label>
    <input type="file" name="pdf_file" accept="application/pdf" required>
    <button type="submit">Upload</button>
</form>
