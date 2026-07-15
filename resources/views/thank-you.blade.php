@extends('layouts.blank')

@section('title', 'Payment Confirmed - Credit Remedi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        font-family: 'Inter', sans-serif;
        overflow: hidden;
    }

    .thankyou-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }

    /* Animated Background Shapes */
    .bg-shapes {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .bg-shapes .shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.08;
        background: #fff;
    }

    .bg-shapes .shape:nth-child(1) {
        width: 400px; height: 400px;
        top: -100px; right: -100px;
        animation: floatShape 8s ease-in-out infinite;
    }

    .bg-shapes .shape:nth-child(2) {
        width: 300px; height: 300px;
        bottom: -80px; left: -80px;
        animation: floatShape 10s ease-in-out infinite reverse;
    }

    .bg-shapes .shape:nth-child(3) {
        width: 200px; height: 200px;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        animation: floatShape 12s ease-in-out infinite;
    }

    @keyframes floatShape {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-30px) scale(1.05); }
    }

    /* Card */
    .thankyou-card {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        max-width: 480px;
        width: 90%;
        text-align: center;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        animation: cardEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes cardEntry {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Checkmark */
    .check-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #34d399, #10b981);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
        animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(0.5);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .check-circle svg {
        width: 40px;
        height: 40px;
        stroke: #fff;
        stroke-width: 3;
        fill: none;
        stroke-dasharray: 50;
        stroke-dashoffset: 50;
        animation: drawCheck 0.5s ease-out 0.6s forwards;
    }

    @keyframes drawCheck {
        to { stroke-dashoffset: 0; }
    }

    .thankyou-card h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
        animation: fadeUp 0.5s ease 0.4s both;
    }

    .thankyou-card .subtitle {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2rem;
        animation: fadeUp 0.5s ease 0.5s both;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Progress bar */
    .progress-track {
        width: 100%;
        height: 4px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.15);
        margin-bottom: 1.5rem;
        overflow: hidden;
        animation: fadeUp 0.5s ease 0.6s both;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #34d399, #a78bfa);
        animation: progressShrink 3s linear 0.8s forwards;
        width: 100%;
    }

    @keyframes progressShrink {
        from { width: 100%; }
        to { width: 0%; }
    }

    /* CTA Button */
    .btn-continue {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        color: #667eea;
        background: #fff;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        animation: fadeUp 0.5s ease 0.7s both;
    }

    .btn-continue:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        background: #f8f9ff;
    }

    .btn-continue:active {
        transform: translateY(0);
    }

    .btn-continue .arrow {
        transition: transform 0.2s ease;
    }

    .btn-continue:hover .arrow {
        transform: translateX(4px);
    }

    /* Countdown text */
    .countdown-text {
        margin-top: 1rem;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.5);
        animation: fadeUp 0.5s ease 0.8s both;
    }
</style>

<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '884432070590327');
fbq('track', 'PageView');
fbq('track', 'Purchase', {currency: 'USD', value: 0.00});
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=884432070590327&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

<div class="thankyou-container">
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Card -->
    <div class="thankyou-card">
        <!-- Animated Checkmark -->
        <div class="check-circle">
            <svg viewBox="0 0 24 24">
                <polyline points="4 12 10 18 20 6"></polyline>
            </svg>
        </div>

        <h1>Payment confirmed 🎉</h1>
        <p class="subtitle">Loading Ally…</p>

        <!-- Auto-redirect progress bar -->
        <div class="progress-track">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <a href="{{ $redirectUrl }}" class="btn-continue" id="continueBtn">
            Continue to Ally
            <span class="arrow">→</span>
        </a>

        <p class="countdown-text">Redirecting in <span id="countdown">3</span>s…</p>
    </div>
</div>

<script>
    (function() {
        const redirectUrl = @json($redirectUrl);
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(function() {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = redirectUrl;
            }
        }, 1000);
    })();
</script>
@endsection
