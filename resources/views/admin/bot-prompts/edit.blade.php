@extends('layouts.app')

@section('title', 'Edit Bot Prompt')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.bot-prompts.index') }}">Bot Prompts</a></li>
                    <li class="breadcrumb-item active">Edit: {{ $botPrompt->name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">✏️ Edit Bot Prompt</h2>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.bot-prompts.update', $botPrompt) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Prompt Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="key" class="form-label">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="key" name="key" 
                                   value="{{ old('key', $botPrompt->key) }}" required>
                            <small class="text-muted">Unique identifier (e.g., system_prompt, template_collection)</small>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $botPrompt->name) }}" required>
                            <small class="text-muted">Human-readable name</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $botPrompt->description) }}</textarea>
                            <small class="text-muted">What does this prompt do?</small>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control font-monospace" id="content" name="content" rows="20" required>{{ old('content', $botPrompt->content) }}</textarea>
                            <small class="text-muted">
                                The actual prompt text. Use placeholders like [Creditor Name], [Account Number], etc.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="system" {{ old('category', $botPrompt->category) == 'system' ? 'selected' : '' }}>System</option>
                                <option value="flow" {{ old('category', $botPrompt->category) == 'flow' ? 'selected' : '' }}>Flow</option>
                                <option value="template" {{ old('category', $botPrompt->category) == 'template' ? 'selected' : '' }}>Template</option>
                                <option value="citation" {{ old('category', $botPrompt->category) == 'citation' ? 'selected' : '' }}>Citation</option>
                                <option value="general" {{ old('category', $botPrompt->category) == 'general' ? 'selected' : '' }}>General</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">Order</label>
                            <input type="number" class="form-control" id="order" name="order" 
                                   value="{{ old('order', $botPrompt->order) }}" min="0">
                            <small class="text-muted">Display order (lower = first)</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" 
                                   {{ old('active', $botPrompt->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Active
                            </label>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Prompt
                            </button>
                            <a href="{{ route('admin.bot-prompts.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">💡 Tips</h6>
                    </div>
                    <div class="card-body">
                        <small>
                            <strong>Placeholders:</strong><br>
                            • [Creditor Name]<br>
                            • [Account Number]<br>
                            • [Date]<br>
                            • [Consumer Name]<br>
                            • [Bureau]<br>
                            <br>
                            <strong>Categories:</strong><br>
                            • <strong>System:</strong> Core AI behavior<br>
                            • <strong>Flow:</strong> Conversation steps<br>
                            • <strong>Template:</strong> Letter templates<br>
                            • <strong>Citation:</strong> Legal references<br>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Preview Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📄 Preview</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $botPrompt->content }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
}
</style>
@endsection
