{{-- Ally AI Guided Tour Component --}}

@if(!auth()->user()->tour_completed)
<div id="allyTour" class="ally-tour-overlay">
    {{-- Dark Overlay --}}
    <div class="ally-tour-backdrop"></div>
    
    {{-- Spotlight for highlighted elements --}}
    <div id="tourSpotlight" class="tour-spotlight"></div>
    
    {{-- Floating Ally Mascot --}}
    <div id="allyMascot" class="ally-mascot">
        <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" class="ally-mascot-img">
    </div>
    
    {{-- Speech Bubble --}}
    <div id="allySpeechBubble" class="ally-speech-bubble">
        <button id="closeTourBtn" class="tour-close-x" onclick="completeTour()">&times;</button>
        <div class="speech-bubble-tail"></div>
        <div class="speech-bubble-content">
            <p id="tourMessage" class="tour-message"></p>
            <div id="tourButtons" class="tour-buttons"></div>
        </div>
        <div class="tour-progress">
            <span id="tourStep">1</span> of <span id="tourTotal">5</span>
        </div>
    </div>
</div>

<style>
    /* Tour Overlay */
    .ally-tour-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .ally-tour-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
    
    /* Dark Backdrop */
    .ally-tour-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        pointer-events: none;
    }
    
    /* Spotlight Effect */
    .tour-spotlight {
        position: absolute;
        border-radius: 12px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.75),
                    0 0 40px rgba(102, 126, 234, 0.5);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        z-index: 10001;
    }
    
    /* Floating Ally Mascot */
    .ally-mascot {
        position: fixed;
        z-index: 10003;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        animation: allyFloat 3s ease-in-out infinite;
    }
    
    .ally-mascot-img {
        width: 120px;
        height: 120px;
        filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.3));
        animation: allyPulse 2s ease-in-out infinite;
    }
    
    @keyframes allyFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes allyPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    /* Speech Bubble */
    .ally-speech-bubble {
        position: fixed;
        background: white;
        border-radius: 20px;
        padding: 24px;
        max-width: 420px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        z-index: 10004;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        animation: bubbleAppear 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .tour-close-x {
        position: absolute;
        top: 10px;
        right: 15px;
        background: none;
        border: none;
        font-size: 24px;
        color: #718096;
        cursor: pointer;
        line-height: 1;
        padding: 5px;
        z-index: 10;
    }
    
    @keyframes bubbleAppear {
        0% { opacity: 0; transform: scale(0.8) translateY(20px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    /* Speech Bubble Tail */
    .speech-bubble-tail {
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Dark mode support */
    [data-theme="dark"] .ally-speech-bubble {
        background: #2d3748;
        color: #f7fafc;
    }
    
    [data-theme="dark"] .tour-message {
        color: #f7fafc;
    }
    
    /* Speech Bubble Content */
    .speech-bubble-content {
        position: relative;
    }
    
    .tour-message {
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 20px;
        color: #1a202c;
        white-space: pre-line;
    }
    
    /* Tour Buttons */
    .tour-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .tour-btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
        min-width: 120px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    .tour-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .tour-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .tour-btn-secondary {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    .tour-btn-secondary:hover {
        background: rgba(102, 126, 234, 0.1);
    }
    
    /* Tour Progress */
    .tour-progress {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        text-align: center;
        font-size: 0.875rem;
        color: #718096;
        font-weight: 500;
    }
    
    [data-theme="dark"] .tour-progress {
        border-top-color: rgba(255, 255, 255, 0.1);
        color: #a0aec0;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .ally-mascot {
            transition: none !important;
            animation: allyFloatMobile 3s ease-in-out infinite;
        }

        .ally-mascot-img {
            width: 70px;
            height: 70px;
        }
        
        .ally-speech-bubble {
            max-width: 94vw;
            width: 94vw;
            padding: 20px 16px 16px 16px;
            font-size: 0.9rem;
            border-radius: 16px;
            left: 50% !important;
            transform: translateX(-50%) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        
        .tour-message {
            font-size: 0.875rem;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        .tour-btn {
            padding: 12px 12px;
            font-size: 0.85rem;
            min-width: 80px;
        }
        
        .tour-progress {
            font-size: 0.75rem;
            margin-top: 12px;
            padding-top: 12px;
        }
        
        .tour-spotlight {
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.85);
        }

        .speech-bubble-tail {
            display: none !important;
        }

        @keyframes allyFloatMobile {
            0%, 100% { transform: translate(-50%, 0px); }
            50% { transform: translate(-50%, -8px); }
        }
    }
    
    /* Highlighted Element */
    .tour-highlight {
        position: relative !important;
        z-index: 10002 !important;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.8), 0 0 20px rgba(102, 126, 234, 0.6) !important;
        border-radius: 8px;
        animation: highlightPulse 2s infinite !important;
    }

    @keyframes highlightPulse {
        0% { box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.8), 0 0 0px rgba(102, 126, 234, 0); }
        50% { box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.8), 0 0 25px rgba(102, 126, 234, 0.6); }
        100% { box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.8), 0 0 0px rgba(102, 126, 234, 0); }
    }
</style>

<script>
    // Ally Tour Configuration
    const allyTourSteps = [
        {
            message: "Hey, I'm Ally — your AI Credit Ally 🤝\n\nI'll walk you through how to use your dashboard and fix your credit with confidence.",
            buttons: [
                { text: 'Show Me Around', action: 'next', primary: true },
                { text: 'Skip for Now', action: 'skip', primary: false }
            ],
            target: null,
            position: 'center'
        },
        {
            message: "Analyze your credit report here to identify negative items. This is where the magic happens!",
            buttons: [
                { text: 'Next Step', action: 'next', primary: true }
            ],
            target: 'main .btn-gradient-primary:contains("Start"), main .btn-primary:contains("Let Ally Generate")',
            position: 'bottom'
        },
        {
            message: "Track your filed disputes and letters here. Everything we create lives in this section.",
            buttons: [
                { text: 'Got It', action: 'next', primary: true }
            ],
            target: 'main a[href*="disputes"], main .btn:contains("View All Activity"), main .btn:contains("My Disputes")',
            position: 'bottom'
        },
        {
            message: "Need help? Tap here to chat with me anytime. I'm here to guide you step-by-step.",
            buttons: [
                { text: 'Awesome', action: 'next', primary: true }
            ],
            target: 'main a[href*="credit-repair-bot"], main .btn:contains("Chat with Ally")',
            position: 'bottom'
        },
        {
            message: "Our Credit Vault contains video guides and tutorials to help you master the process.",
            buttons: [
                { text: "Finish Tour", action: 'complete', primary: true }
            ],
            target: 'main a[href*="credit-vault"], main .btn:contains("Upload"), main .btn:contains("Credit Vault")',
            position: 'top'
        }
    ];
    
    let currentTourStep = 0;
    let tourActive = false;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('allyTour')) {
            initializeTour();
        }
    });

    window.addEventListener('resize', () => {
        if (tourActive) showTourStep(currentTourStep);
    });
    
    function initializeTour() {
        currentTourStep = 0;
        tourActive = true;
        const tourOverlay = document.getElementById('allyTour');
        if (tourOverlay) {
            tourOverlay.classList.add('active');
            showTourStep(0);
        }
    }
    
    function showTourStep(stepIndex) {
        if (stepIndex >= allyTourSteps.length) {
            completeTour();
            return;
        }
        
        currentTourStep = stepIndex;
        const step = allyTourSteps[stepIndex];
        const bubble = document.getElementById('allySpeechBubble');
        const spotlight = document.getElementById('tourSpotlight');
        const message = document.getElementById('tourMessage');
        const buttons = document.getElementById('tourButtons');
        const stepNumber = document.getElementById('tourStep');
        const totalSteps = document.getElementById('tourTotal');
        
        if (!bubble || !message || !buttons) return;

        stepNumber.textContent = stepIndex + 1;
        totalSteps.textContent = allyTourSteps.length;
        message.textContent = step.message;
        
        buttons.innerHTML = '';
        step.buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = `tour-btn \${btn.primary ? 'tour-btn-primary' : 'tour-btn-secondary'}`;
            button.textContent = btn.text;
            button.onclick = (e) => {
                e.stopPropagation();
                handleTourAction(btn.action);
            };
            buttons.appendChild(button);
        });
        
        if (step.target) {
            let targetEl = null;
            const selectors = step.target.split(',').map(s => s.trim());
            
            for (const selector of selectors) {
                if (selector.includes(':contains')) {
                    const baseSelector = selector.split(':contains')[0].trim() || 'a, button';
                    const text = selector.match(/:contains\("(.+)"\)/)[1];
                    targetEl = Array.from(document.querySelectorAll(baseSelector))
                                   .find(el => el.textContent.trim().includes(text));
                } else {
                    targetEl = document.querySelector(selector);
                }
                if (targetEl && targetEl.offsetParent !== null) break;
            }
            
            if (targetEl) {
                highlightElement(targetEl, step.position);
            } else {
                positionCenter();
            }
        } else {
            positionCenter();
        }
        
        bubble.style.animation = 'none';
        setTimeout(() => {
            bubble.style.animation = 'bubbleAppear 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
        }, 10);
    }
    
    function highlightElement(element, position) {
        const mascot = document.getElementById('allyMascot');
        const bubble = document.getElementById('allySpeechBubble');
        const spotlight = document.getElementById('tourSpotlight');
        
        const isMobile = window.innerWidth <= 768;
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
            const rect = element.getBoundingClientRect();
            
            spotlight.style.display = 'block';
            spotlight.style.top = `\${rect.top - 8}px`;
            spotlight.style.left = `\${rect.left - 8}px`;
            spotlight.style.width = `\${rect.width + 16}px`;
            spotlight.style.height = `\${rect.height + 16}px`;
            
            document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
            element.classList.add('tour-highlight');
            
            bubble.style.top = 'auto';
            bubble.style.bottom = 'auto';
            bubble.style.left = 'auto';
            bubble.style.right = 'auto';
            mascot.style.transform = 'none';
            bubble.style.transform = 'none';

            if (isMobile) {
                mascot.style.display = 'none';
                bubble.style.left = '50%';
                const spaceBelow = window.innerHeight - rect.bottom;
                if (spaceBelow < 280) {
                    bubble.style.bottom = `\${window.innerHeight - rect.top + 15}px`;
                } else {
                    bubble.style.top = `\${rect.bottom + 15}px`;
                }
            } else {
                mascot.style.display = 'block';
                const tail = document.querySelector('.speech-bubble-tail');
                const bubbleHeight = bubble.offsetHeight || 180;
                const bubbleWidth = bubble.offsetWidth || 350;
                
                // Desktop side-by-side positioning
                let baseTop, baseLeft;
                if (position === 'top' || (window.innerHeight - rect.bottom < 250)) {
                    baseTop = rect.top - Math.max(bubbleHeight, 140) - 40;
                    if (tail) {
                        tail.style.top = 'auto'; tail.style.bottom = '-15px'; tail.style.left = '40px';
                        tail.style.borderWidth = '15px 15px 0 15px';
                        tail.style.borderColor = document.documentElement.getAttribute('data-theme') === 'dark' ? '#2d3748 transparent transparent transparent' : 'white transparent transparent transparent';
                    }
                } else {
                    baseTop = rect.bottom + 20;
                    if (tail) {
                        tail.style.top = '20px'; tail.style.bottom = 'auto'; tail.style.left = '20px';
                        tail.style.borderWidth = '0 15px 15px 15px';
                        tail.style.borderColor = document.documentElement.getAttribute('data-theme') === 'dark' ? 'transparent transparent #2d3748 transparent' : 'transparent transparent white transparent';
                    }
                }
                
                // Align mascot to the left of the element, bubble to the right of mascot
                const mascotLeft = Math.max(10, rect.left - 120);
                const bubbleLeft = Math.max(10, Math.min(window.innerWidth - bubbleWidth - 20, mascotLeft + 110));
                
                mascot.style.top = `\${baseTop}px`;
                mascot.style.left = `\${mascotLeft}px`;
                bubble.style.top = `\${baseTop}px`;
                bubble.style.left = `\${bubbleLeft}px`;
                if (tail) tail.style.display = 'block';
            }
        }, 400); // Increased delay for scroll stabilization
    }
    
    function positionCenter() {
        const mascot = document.getElementById('allyMascot');
        const bubble = document.getElementById('allySpeechBubble');
        const spotlight = document.getElementById('tourSpotlight');
        const isMobile = window.innerWidth <= 768;
        
        if (!bubble || !mascot) return;
        spotlight.style.display = 'none';
        
        if (isMobile) {
            mascot.style.display = 'block';
            mascot.style.top = '15%'; mascot.style.left = '50%'; mascot.style.transform = 'translateX(-50%)';
            bubble.style.top = '50%'; bubble.style.left = '50%'; bubble.style.transform = 'translate(-50%, -50%)';
        } else {
            mascot.style.display = 'block';
            mascot.style.top = '50%'; mascot.style.left = '35%'; mascot.style.transform = 'translate(-50%, -50%)';
            bubble.style.top = '50%'; bubble.style.left = '55%'; bubble.style.transform = 'translate(-50%, -50%)';
            const tail = document.querySelector('.speech-bubble-tail');
            if (tail) {
                tail.style.display = 'block'; tail.style.top = '50%'; tail.style.left = '-15px';
                tail.style.transform = 'translateY(-50%)';
                tail.style.borderWidth = '15px 15px 15px 0';
                tail.style.borderColor = document.documentElement.getAttribute('data-theme') === 'dark' ? 'transparent #2d3748 transparent transparent' : 'transparent white transparent transparent';
            }
        }
    }
    
    function handleTourAction(action) {
        if (action === 'next') {
            currentTourStep++;
            showTourStep(currentTourStep);
        } else if (action === 'skip' || action === 'complete') {
            completeTour();
        }
    }
    
    function completeTour() {
        const tourOverlay = document.getElementById('allyTour');
        tourActive = false;
        if (!tourOverlay) return;
        
        tourOverlay.classList.remove('active');
        document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
        
        fetch('/tour/complete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => {
            setTimeout(() => { tourOverlay.remove(); }, 300);
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (!tourActive) return;
        if (e.key === 'Escape') completeTour();
    });
</script>
@endif
