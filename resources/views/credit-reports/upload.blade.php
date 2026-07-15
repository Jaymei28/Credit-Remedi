@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Upload Credit Report PDF</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('credit-reports.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="pdf_file" class="form-label">Choose PDF file</label>
            <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept="application/pdf" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
