@extends('layouts.blank')

@section('title', 'Choose Your Plan - Credit Remedi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .plans-container {
        min-height: 100vh;
        padding: 3rem 1rem;
        position: relative;
        overflow: hidden;
    }

    /* Animated Background */
    .bg-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }

    .shape {
        position: absolute;
        opacity: 0.05;
        animation: float 20s infinite ease-in-out;
    }

    .shape-1 {
        width: 400px;
        height: 400px;
        background: white;
        border-radius: 50%;
        top: -150px;
        right: -100px;
    }

    .shape-2 {
        width: 300px;
        height: 300px;
        background: white;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        bottom: -100px;
        left: -50px;
        animation-delay: 3s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-30px) rotate(180deg);
        }
    }

    .plans-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
        z-index: 1;
        animation: fadeInDown 0.8s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .logo-img {
        height: 70px;
        margin-bottom: 1.5rem;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .plans-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 0.5rem;
    }

    .plans-subtitle {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .plan-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        position: relative;
        animation: fadeInUp 0.8s ease;
        animation-fill-mode: both;
    }

    .plan-card:nth-child(1) {
        animation-delay: 0.2s;
    }

    .plan-card:nth-child(2) {
        animation-delay: 0.4s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .plan-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .plan-card.popular {
        border: 3px solid #10b981;
        transform: scale(1.05);
    }

    .plan-card.popular:hover {
        transform: scale(1.05) translateY(-10px);
    }

    .popular-badge {
        position: absolute;
        top: -15px;
        right: 30px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    .plan-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .plan-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .plan-card:nth-child(1) .plan-name {
        color: #667eea;
    }

    .plan-card.popular .plan-name {
        color: #10b981;
    }

    .plan-price {
        font-size: 3rem;
        font-weight: 800;
        color: #1f2937 !important;
        line-height: 1;
    }

    .plan-price-currency {
        font-size: 1.5rem;
        vertical-align: super;
        color: #1f2937 !important;
    }

    .plan-price-period {
        font-size: 1rem;
        color: #6b7280 !important;
        font-weight: 500;
    }

    .plan-features {
        list-style: none;
        padding: 0;
        margin-bottom: 2rem;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        color: #374151 !important;
    }

    .feature-item span {
        color: #374151 !important;
    }

    .feature-item.excluded span {
        color: #6b7280 !important;
    }

    .feature-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .feature-icon.included {
        background: #d1fae5;
        color: #059669;
    }

    .feature-icon.excluded {
        background: #fee2e2;
        color: #dc2626;
    }

    .feature-item.excluded {
        opacity: 0.5;
    }

    .plan-button {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .plan-button.starter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .plan-button.starter:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .plan-button.premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .plan-button.premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }

    /* IdentityIQ Notice Banner */
    .identityiq-notice {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #3b82f6;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #1e40af !important;
        font-weight: 600;
    }

    .identityiq-notice .icon {
        font-size: 1.1rem;
        flex-shrink: 0;
        color: #1e40af !important;
    }

    .identityiq-notice span {
        color: #1e40af !important;
    }

    /* Feature Section Headers */
    .feature-section-header {
        font-size: 0.85rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .feature-section-header:first-child {
        margin-top: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .plans-title {
            font-size: 2rem;
        }

        .plans-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .plan-card.popular {
            transform: scale(1);
        }

        .plan-card.popular:hover {
            transform: translateY(-10px);
        }

        .plan-price {
            font-size: 2.5rem;
        }
    }
</style>

<div class="plans-container">
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <!-- Header -->
    <div class="plans-header">
        <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi" class="logo-img">
        <h1 class="plans-title">Choose Your Plan</h1>
        <p class="plans-subtitle">Select a plan to proceed with registration</p>
    </div>

    <!-- Plans Grid -->
    <div class="plans-grid">
        <!-- Starter Plan -->
        <div class="plan-card">
            <div class="plan-header">
                <div class="plan-name">Standard Plan (Starter)</div>
                <div class="plan-price">
                    <span class="plan-price-currency">$</span>49.99<span class="plan-price-period">/month</span>
                </div>
            </div>

            <!-- IdentityIQ Notice -->
            <div class="identityiq-notice">
                <span class="icon">✔</span>
                <span>IdentityIQ credit monitoring is required for accurate tracking & results</span>
            </div>

            <ul class="plan-features">
                <!-- Core AI Tools Section -->
                <div class="feature-section-header">Core AI Tools</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>AI-Powered Dispute Letter Generator</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Consumer Law Citation Assistance</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Guided Step-by-Step Workflows</span>
                </li>

                <!-- Automation & Tracking Section -->
                <div class="feature-section-header">Automation & Tracking</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>15-Day Automated Follow-Up Letters</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Auto Dispute Timeline Reminders</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Monthly Credit Progress Dashboard</span>
                </li>

                <!-- Support & Access Section -->
                <div class="feature-section-header">Support & Access</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>AI Chat Assistant (24/7)</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Priority Updates & Feature Access</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Private Community Access</span>
                </li>

                <!-- Additional Features -->
                <div class="feature-section-header">Additional Features</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Direct Bureau & Regulator Filing Access</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Funding Readiness Score + Approval Roadmap</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Fundability Score & Lender Matching</span>
                </li>
                <li class="feature-item excluded">
                    <span class="feature-icon excluded">✗</span>
                    <span>Free Digital Credit Journal (Downloadable)</span>
                </li>
            </ul>

            <a href="/register?plan=starter" class="plan-button starter">Choose Starter</a>
        </div>

        <!-- Premium Plan -->
        <div class="plan-card popular">
            <div class="popular-badge">🔥 MOST POPULAR</div>
            
            <div class="plan-header">
                <div class="plan-name">Pro Plan (Turbo)</div>
                <div class="plan-price">
                    <span class="plan-price-currency">$</span>69.99<span class="plan-price-period">/month</span>
                </div>
            </div>

            <!-- IdentityIQ Notice -->
            <div class="identityiq-notice">
                <span class="icon">✔</span>
                <span>IdentityIQ credit monitoring is required for accurate tracking & results</span>
            </div>

            <ul class="plan-features">
                <!-- Core AI Tools Section -->
                <div class="feature-section-header">Core AI Tools</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>AI-Powered Dispute Letter Generator</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Consumer Law Citation Assistance</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Guided Step-by-Step Workflows</span>
                </li>

                <!-- Automation & Tracking Section -->
                <div class="feature-section-header">Automation & Tracking</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>15-Day Automated Follow-Up Letters</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Auto Dispute Timeline Reminders</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Monthly Credit Progress Dashboard</span>
                </li>

                <!-- Support & Access Section -->
                <div class="feature-section-header">Support & Access</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>AI Chat Assistant (24/7)</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Priority Updates & Feature Access</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Private Community Access</span>
                </li>

                <!-- Additional Features -->
                <div class="feature-section-header">Additional Features</div>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Direct Bureau & Regulator Filing Access</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Funding Readiness Score + Approval Roadmap</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Fundability Score & Lender Matching</span>
                </li>
                <li class="feature-item">
                    <span class="feature-icon included">✓</span>
                    <span>Free Digital Credit Journal (Downloadable)</span>
                </li>
            </ul>

            <a href="/register?plan=premium" class="plan-button premium">Choose Premium</a>
        </div>
    </div>
</div>
@endsection
