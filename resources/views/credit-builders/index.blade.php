@extends('layouts.app')

@section('title', 'Credit Builders')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .credit-builders-container {
        font-family: 'Inter', sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .builders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .builder-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .builder-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .builder-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .builder-card:hover::before {
        opacity: 1;
    }

    .builder-icon {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        background: white;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .builder-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .builder-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }

    .builder-title {
        font-size: 1rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .builder-description {
        font-size: 0.9rem;
        color: #9ca3af;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .promo-badge {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: inline-block;
    }

    .promo-code {
        background: #f3f4f6;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #667eea;
    }

    .promo-code-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .promo-code-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #667eea;
        font-family: 'Courier New', monospace;
    }

    .visit-btn {
        width: 100%;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        margin-top: auto;
    }

    .visit-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        color: white;
    }

    /* Dark Mode Support */
    [data-theme="dark"] .credit-builders-container {
        background-color: transparent;
    }

    [data-theme="dark"] .page-subtitle {
        color: #9ca3af;
    }

    [data-theme="dark"] .builder-card {
        background: #1f2937;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        border-color: #374151;
    }

    [data-theme="dark"] .builder-card:hover {
        background: #1f2937;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.5);
        border-color: #667eea;
    }

    [data-theme="dark"] .builder-name {
        color: #f3f4f6;
    }

    [data-theme="dark"] .builder-title {
        color: #d1d5db;
    }

    [data-theme="dark"] .builder-description {
        color: #9ca3af;
    }

    [data-theme="dark"] .promo-code {
        background: #374151;
        border-color: #667eea;
    }

    [data-theme="dark"] .promo-code-label {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 2rem;
        }

        .builders-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .builder-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="credit-builders-container">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">Credit Builders</h1>
        <p class="page-subtitle">Build your credit history by reporting rent and utility payments</p>
    </div>

    <!-- Builders Grid -->
    <div class="builders-grid">
        @foreach($creditBuilders as $builder)
        <div class="builder-card">
            <div class="builder-icon">
                <img src="/{{ $builder['logo'] }}" alt="{{ $builder['name'] }} Logo" class="builder-logo" onerror="this.style.display='none'">
            </div>

            <h3 class="builder-name">{{ $builder['name'] }}</h3>
            <p class="builder-title">{{ $builder['title'] }}</p>
            <p class="builder-description">{{ $builder['description'] }}</p>

            @if($builder['promo_code'])
            <div class="promo-badge">
                Special Offer Available
            </div>
            <div class="promo-code">
                <div class="promo-code-label">Promo Code</div>
                <div class="promo-code-value">{{ $builder['promo_code'] }}</div>
                <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                    {{ $builder['promo_description'] }}
                </small>
            </div>
            @endif

            <a href="{{ $builder['url'] }}" target="_blank" class="visit-btn">
                <i class="bi bi-box-arrow-up-right me-2"></i>Visit {{ $builder['name'] }}
            </a>
        </div>
        @endforeach
    </div>

    <!-- Info Section -->
    <div class="alert alert-info" style="border-left: 4px solid #3b82f6;">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Pro Tip:</strong> Using multiple credit builders can help diversify your credit mix and accelerate your credit building journey. Make sure to pay all bills on time for maximum impact!
    </div>
</div>
@endsection
