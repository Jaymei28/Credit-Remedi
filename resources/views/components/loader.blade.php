{{-- 
    Reusable Loader Component
    Usage: @include('components.loader', ['id' => 'myLoader', 'message' => 'Loading...'])
--}}

@php
    $loaderId = $id ?? 'globalLoader';
    $message = $message ?? 'Loading...';
@endphp

<div id="{{ $loaderId }}" class="loader-overlay" style="display: none;">
    <div class="loader-container">
        <div class="loader-spinner">
            <svg class="spinner-ring" viewBox="0 0 100 100">
                <circle class="spinner-circle" cx="50" cy="50" r="45"></circle>
            </svg>
            <div class="loader-logo">
                <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi">
            </div>
        </div>
        <p class="loader-message">{{ $message }}</p>
    </div>
</div>

<style>
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .loader-container {
        text-align: center;
        animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .loader-spinner {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 1.5rem;
    }

    .spinner-ring {
        width: 120px;
        height: 120px;
        position: absolute;
        top: 0;
        left: 0;
        animation: rotate 2s linear infinite;
    }

    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .spinner-circle {
        fill: none;
        stroke: url(#gradient);
        stroke-width: 4;
        stroke-linecap: round;
        stroke-dasharray: 283;
        stroke-dashoffset: 75;
        animation: dash 1.5s ease-in-out infinite;
    }

    @keyframes dash {
        0% {
            stroke-dashoffset: 283;
        }
        50% {
            stroke-dashoffset: 75;
        }
        100% {
            stroke-dashoffset: 283;
        }
    }

    .loader-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #1a1a1a;
        border-radius: 50%;
        padding: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.05);
        }
    }

    .loader-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .loader-message {
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        animation: fadeInOut 2s ease-in-out infinite;
    }

    @keyframes fadeInOut {
        0%, 100% {
            opacity: 0.7;
        }
        50% {
            opacity: 1;
        }
    }

    /* Dark mode support */
    [data-theme="dark"] .loader-overlay {
        background: rgba(0, 0, 0, 0.85);
    }

    [data-theme="dark"] .loader-logo {
        background: #1f2937;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
</style>

<!-- SVG Gradient Definition -->
<svg width="0" height="0" style="position: absolute;">
    <defs>
        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
        </linearGradient>
    </defs>
</svg>

<script>
    // Helper functions to show/hide loader
    window.showLoader = function(loaderId = 'globalLoader', message = null) {
        const loader = document.getElementById(loaderId);
        if (loader) {
            if (message) {
                const messageEl = loader.querySelector('.loader-message');
                if (messageEl) messageEl.textContent = message;
            }
            loader.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    window.hideLoader = function(loaderId = 'globalLoader') {
        const loader = document.getElementById(loaderId);
        if (loader) {
            loader.style.display = 'none';
            document.body.style.overflow = '';
        }
    };
</script>
