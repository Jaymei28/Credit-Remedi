@extends('layouts.app')

@section('title', 'Loader Demo')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-soft">
                <div class="card-header">
                    <h5 class="mb-0">🔄 Loader Component Demo</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Click the buttons below to test different loader variations:</p>
                    
                    <div class="d-grid gap-3">
                        <button class="btn btn-gradient-primary" onclick="showLoaderDemo('loader1', 'Loading your data...')">
                            <i class="bi bi-play-circle me-2"></i>Show Default Loader
                        </button>
                        
                        <button class="btn btn-gradient-info" onclick="showLoaderDemo('loader2', 'Processing your request...')">
                            <i class="bi bi-gear me-2"></i>Show Processing Loader
                        </button>
                        
                        <button class="btn btn-gradient-success" onclick="showLoaderDemo('loader3', 'Uploading files...')">
                            <i class="bi bi-cloud-upload me-2"></i>Show Upload Loader
                        </button>
                        
                        <button class="btn btn-gradient-warning" onclick="showLoaderDemo('loader4', 'Analyzing your credit report...')">
                            <i class="bi bi-graph-up me-2"></i>Show Analysis Loader
                        </button>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">📝 How to Use:</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-2"><strong>1. Include the loader component:</strong></p>
                        <code class="d-block mb-3 p-2 bg-white rounded">
                            @include('components.loader', ['id' => 'myLoader', 'message' => 'Loading...'])
                        </code>

                        <p class="mb-2"><strong>2. Show the loader:</strong></p>
                        <code class="d-block mb-3 p-2 bg-white rounded">
                            showLoader('myLoader', 'Custom message');
                        </code>

                        <p class="mb-2"><strong>3. Hide the loader:</strong></p>
                        <code class="d-block p-2 bg-white rounded">
                            hideLoader('myLoader');
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Loaders -->
@include('components.loader', ['id' => 'loader1', 'message' => 'Loading...'])
@include('components.loader', ['id' => 'loader2', 'message' => 'Processing...'])
@include('components.loader', ['id' => 'loader3', 'message' => 'Uploading...'])
@include('components.loader', ['id' => 'loader4', 'message' => 'Analyzing...'])

@endsection

@push('scripts')
<script>
    function showLoaderDemo(loaderId, message) {
        showLoader(loaderId, message);
        
        // Auto-hide after 3 seconds for demo
        setTimeout(() => {
            hideLoader(loaderId);
        }, 3000);
    }
</script>
@endpush
