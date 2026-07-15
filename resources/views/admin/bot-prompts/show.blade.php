@extends('layouts.app')

@section('title', 'View Bot Prompt')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.bot-prompts.index') }}">Bot Prompts</a></li>
                    <li class="breadcrumb-item active">{{ $botPrompt->name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">{{ $botPrompt->name }}</h2>
            <p class="text-muted">{{ $botPrompt->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.bot-prompts.edit', $botPrompt) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <a href="{{ route('admin.bot-prompts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Content</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $botPrompt->content }}</pre>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Key:</dt>
                        <dd class="col-sm-8"><code>{{ $botPrompt->key }}</code></dd>

                        <dt class="col-sm-4">Category:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $botPrompt->category == 'system' ? 'primary' : ($botPrompt->category == 'template' ? 'success' : 'info') }}">
                                {{ ucfirst($botPrompt->category) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Order:</dt>
                        <dd class="col-sm-8">{{ $botPrompt->order }}</dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $botPrompt->active ? 'success' : 'secondary' }}">
                                {{ $botPrompt->active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">{{ $botPrompt->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8">{{ $botPrompt->updated_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.bot-prompts.toggle', $botPrompt) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-{{ $botPrompt->active ? 'warning' : 'success' }} w-100">
                                <i class="bi bi-{{ $botPrompt->active ? 'pause' : 'play' }}-circle me-2"></i>
                                {{ $botPrompt->active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.bot-prompts.destroy', $botPrompt) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this prompt?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash me-2"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
