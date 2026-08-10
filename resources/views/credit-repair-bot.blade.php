@extends('layouts.app')

@section('title', 'Ally AI')

@section('content')
@push('styles')
<style>
    /* 
    💬 CONTEMPORARY CHAT UI 
    Inspired by Messenger/WhatsApp/Apple Messages
    */

    /* Card Container Overrides */
    .chat-card {
        height: 85vh; /* Fixed height for the card */
        max-height: 800px;
        border: none;
        box-shadow: var(--shadow-lg, 0 10px 15px -3px rgba(0, 0, 0, 0.1));
        border-radius: 1rem;
        overflow: hidden; /* contain children */
        display: flex;
        flex-direction: column;
    }

    /* Main Container */
    .chat-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: var(--bg-secondary, #f0f2f5);
        overflow: hidden; /* Prevent double scroll */
    }

    /* Chat Area */
    .chat-container {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        scroll-behavior: smooth;
    }

    /* Message Wrapper */
    .message-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 0.75rem;
        max-width: 85%;
    }
    
    .message-wrapper.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    /* Avatar */
    .chat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex-shrink: 0;
        overflow: hidden;
    }
    
    .chat-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Message Bubble */
    .message-bubble {
        padding: 0.75rem 1rem;
        border-radius: 1.2rem;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.5;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        word-wrap: break-word;
    }

    /* Assistant Styles */
    .message-wrapper.assistant .message-bubble {
        background-color: #ffffff;
        color: #1a1a1a;
        border-bottom-left-radius: 0.2rem;
    }
    
    /* User Styles */
    .message-wrapper.user .message-bubble {
        background-color: var(--bg-primary-accent, #0d6efd); /* Primary Blue */
        color: #ffffff;
        border-bottom-right-radius: 0.2rem;
    }

    /* Timestamp */
    .message-time {
        font-size: 0.7rem;
        color: #8e8e93;
        margin-top: 0.3rem;
        text-align: right;
        opacity: 0.8;
    }
    
    .message-wrapper.user .message-time {
        color: rgba(255,255,255,0.8);
        text-align: left;
    }

    /* Input Area */
    .input-area {
        background-color: #ffffff;
        padding: 1rem;
        border-top: 1px solid var(--border-color, #dee2e6);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .input-row {
        display: flex;
        align-items: flex-end;
        gap: 0.75rem;
        background-color: #f0f2f5;
        border-radius: 1.5rem;
        padding: 0.5rem 0.75rem 0.5rem 1.25rem;
        border: 1px solid transparent;
        transition: border-color 0.2s;
    }

    .input-row:focus-within {
        border-color: #0d6efd;
        background-color: #fff;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
    }

    textarea#messageInput {
        border: none;
        background: transparent;
        resize: none;
        width: 100%;
        max-height: 120px;
        min-height: 24px;
        padding: 0;
        margin-bottom: 4px; /* Align with button */
        outline: none;
        color: #000;
    }
    
    textarea#messageInput:focus {
        box-shadow: none;
    }

    #sendBtn {
        border-radius: 50%;
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        scrollbar-width: thin;
    }
    
    .quick-btn {
        background-color: #e9ecef;
        border: none;
        border-radius: 1rem;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        color: #495057;
        white-space: nowrap;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .quick-btn:hover {
        background-color: #dde0e3;
        color: #000;
    }

    /* Typing Indicator */
    .typing-wrapper {
        margin-left: 3rem; /* Align with text start */
        margin-bottom: 1rem;
        display: none; /* Hidden by default */
    }
    
    .typing-bubble {
        background-color: #fff;
        padding: 0.75rem 1rem;
        border-radius: 1.2rem;
        border-bottom-left-radius: 0.2rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .typing-dot {
        width: 6px;
        height: 6px;
        background-color: #b0b0b0;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out both;
    }
    
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typingBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Header */
    .chat-header {
        background-color: #ffffff;
        color: #212529;
        border-bottom: 1px solid var(--border-color, #dee2e6);
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: between;
        z-index: 10;
    }

    /* 🌙 DARK MODE SUPPORT */
    [data-theme="dark"] .chat-card {
        background-color: #111827;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    }
    
    [data-theme="dark"] .chat-header {
        background-color: #1f2937; /* Secondary Dark */
        color: #f9fafb;
        border-bottom-color: #374151;
    }

    [data-theme="dark"] .chat-wrapper {
        background-color: #111827; /* Match card bg */
    }

    [data-theme="dark"] .message-wrapper.assistant .message-bubble {
        background-color: #1f2937; /* Darker Bubble */
        color: #f9fafb;
    }
    
    [data-theme="dark"] .chat-avatar {
        background-color: #374151;
    }
    
    [data-theme="dark"] .message-time {
        color: #9ca3af;
    }

    [data-theme="dark"] .input-area {
        background-color: #1f2937;
        border-top-color: #374151;
    }
    
    [data-theme="dark"] .input-row {
        background-color: #374151;
        border-color: #4b5563; /* Slight border for visibility */
    }
    
    [data-theme="dark"] textarea#messageInput {
        color: #f9fafb;
    }
    
    [data-theme="dark"] .quick-btn {
        background-color: #374151;
        color: #e5e7eb;
        border: 1px solid #4b5563;
    }
    
    [data-theme="dark"] .quick-btn:hover {
        background-color: #4b5563;
        color: #fff;
    }
    
    [data-theme="dark"] .typing-bubble {
        background-color: #1f2937;
    }
    
    [data-theme="dark"] .typing-dot {
        background-color: #6b7280;
    }

    /* Markdown Styles */
    .markdown-content ul { padding-left: 1.2rem; margin-bottom: 0.5rem; }
    .markdown-content p { margin-bottom: 0.5rem; }
    .markdown-content p:last-child { margin-bottom: 0; }

    /* Quick Reply Buttons (in message bubbles) */
    .quick-reply-buttons {
        margin-top: 0.75rem !important;
    }
    
    .quick-reply-buttons .btn {
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .quick-reply-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .quick-reply-buttons .btn:active {
        transform: translateY(0);
    }
    
    /* Autocomplete Dropdown */
    .autocomplete-dropdown {
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0; /* Changed from 60px to 0 to match input width */
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        margin-bottom: 8px;
    }
    
    .autocomplete-items {
        padding: 0;
        margin: 0;
    }
    
    .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    
    .autocomplete-item:hover {
        background-color: #f8f9fa;
    }
    
    .autocomplete-item.active {
        background-color: #e7f3ff;
    }
    
    .autocomplete-item-name {
        font-weight: 500;
        color: #212529;
    }
    
    .autocomplete-item-type {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: capitalize;
        padding: 2px 8px;
        background: #e9ecef;
        border-radius: 12px;
    }
    
    /* Dark Mode for Autocomplete */
    [data-theme="dark"] .autocomplete-dropdown {
        background: #2d3748;
        border-color: #4a5568;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.4);
    }
    
    [data-theme="dark"] .autocomplete-item {
        border-bottom-color: #4a5568;
    }
    
    [data-theme="dark"] .autocomplete-item:hover {
        background-color: #374151;
    }
    
    [data-theme="dark"] .autocomplete-item.active {
        background-color: #1e40af;
    }
    
    [data-theme="dark"] .autocomplete-item-name {
        color: #e2e8f0;
    }
    
    [data-theme="dark"] .autocomplete-item-type {
        color: #cbd5e0;
        background: #4a5568;
    }
    
    /* Dark Mode for Modal */
    [data-theme="dark"] .modal-content {
        background-color: #2d3748;
        color: #e2e8f0;
    }
    
    [data-theme="dark"] .modal-header,
    [data-theme="dark"] .modal-footer {
        border-color: #4a5568;
    }
    
    [data-theme="dark"] .btn-close {
        filter: invert(1);
    }
    
    [data-theme="dark"] .quick-reply-buttons .btn {
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    [data-theme="dark"] .quick-reply-buttons .btn:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.4);
    }
</style>
@endpush

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <div class="chat-card">
                {{-- Header --}}
                <div class="chat-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; overflow: hidden;">
                            <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Ally AI</h6>
                            <small class="text-success fw-medium" style="font-size: 0.75rem;">● Online</small>
                        </div>
                    </div>
                </div>

                {{-- Wrapper for content --}}
                <div class="chat-wrapper">
                    {{-- Main Chat Area --}}
                    <div id="chat-container" class="chat-container">
                        
                        {{-- Loop through session messages --}}
                        @foreach($messages as $msg)
                            <div class="message-wrapper {{ $msg['role'] }}">
                                {{-- Assistant Avatar --}}
                                @if($msg['role'] === 'assistant')
                                    <div class="chat-avatar">
                                        <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @endif

                                <div class="message-bubble">
                                    @if ($msg['role'] === 'assistant')
                                        <div class="markdown-content" data-md="{{ base64_encode($msg['content']) }}"></div>
                                        
                                        {{-- Quick Reply Buttons --}}
                                        @if(isset($msg['options']) && is_array($msg['options']) && count($msg['options']) > 0)
                                            <div class="d-flex flex-wrap gap-2 mt-3 quick-reply-buttons">
                                                @foreach($msg['options'] as $option)
                                                    <button 
                                                        class="btn btn-sm btn-primary rounded-pill px-3 py-2" 
                                                        style="font-size: 0.875rem;"
                                                         onclick="@if(isset($option['value']) && $option['value'] === 'View Disputes')window.location.href='/my-disputes';@else window.handleSendMessage('{{ addslashes($option['value'] ?? $option['label']) }}');@endif">
                                                        {{ $option['label'] }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        {{ $msg['content'] }}
                                    @endif
                                    
                                    {{-- Timestamp --}}
                                    @if(isset($msg['timestamp']))
                                        <div class="message-time">
                                            {{ \Carbon\Carbon::parse($msg['timestamp'])->format('h:i A') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Typing Indicator --}}
                        <div id="typing-indicator" class="typing-wrapper">
                             <div class="chat-avatar" style="position: absolute; left: 1.5rem; width: 28px; height: 28px;">
                                <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="typing-bubble">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>
                        
                    </div>

                    {{-- Input Area --}}
                    <div class="input-area">
                        {{-- Quick Replies --}}
                        <div class="quick-actions">
                            <button class="quick-btn" onclick="fillInput('Yes')">Yes</button>
                            <button class="quick-btn" onclick="fillInput('No')">No</button>
                            
                            <form method="POST" action="/credit-repair-bot/reset" id="resetForm" class="ms-auto" style="display: none;">
                                @csrf
                            </form>
                            
                            <button type="button" id="resetBtn" class="quick-btn text-danger bg-danger-subtle border-0 ms-auto">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>

                        <form id="sendForm" data-no-loader>
                            @csrf
                            <div class="input-row" style="position: relative;">
                                <textarea id="messageInput" name="message" rows="1" placeholder="Type your message..." required></textarea>
                                
                                {{-- Autocomplete Dropdown --}}
                                <div id="autocompleteDropdown" class="autocomplete-dropdown" style="display: none;">
                                    <div class="autocomplete-items"></div>
                                </div>
                                
                                <button type="submit" id="sendBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

{{-- Reset Confirmation Modal --}}
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="resetModalLabel">
                    <i class="bi bi-arrow-counterclockwise text-danger me-2"></i>
                    Start a New Conversation?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">This will clear your conversation history. Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmResetBtn">
                    <i class="bi bi-check-circle me-1"></i> Yes, Reset
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // v2.0 - Guided Conversation with Quick Replies
    // Global helper for quick buttons (static ones at bottom)
    function fillInput(text) {
        const input = document.getElementById('messageInput');
        input.value = text;
        input.focus();
        input.dispatchEvent(new Event('input')); // Trigger resize/enable button
    }

    document.addEventListener("DOMContentLoaded", function () {
        const chatBox = document.getElementById("chat-container");
        const sendForm = document.getElementById("sendForm");
        const sendBtn = document.getElementById("sendBtn");
        const messageInput = document.getElementById("messageInput");
        const typingIndicator = document.getElementById("typing-indicator");

        // Auto-generate dispute letter if coming from AI Analysis
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('dispute') === 'true') {
            const creditor = urlParams.get('creditor');
            const accountType = urlParams.get('account_type');
            const status = urlParams.get('status');
            const bureau = urlParams.get('bureau');
            const reason = urlParams.get('reason');
            
            // Build the auto-message
            const autoMessage = `Generate a dispute letter for this account:\n\nCreditor: ${creditor}\nAccount Type: ${accountType}\nStatus: ${status}\nBureau: ${bureau}\nDispute Reason: ${reason}\n\nPlease create a professional dispute letter I can send to the credit bureau.`;
            
            // Wait for page to fully load, then send the message
            setTimeout(() => {
                window.handleSendMessage(autoMessage);
                // Clean URL to remove parameters
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 1000);
        }

        // Helper: Scroll to bottom
        const scrollToBottom = () => {
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        // Helper: UTF-8 Safe Base64 Encode
        function b64EncodeUnicode(str) {
            return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
                function toSolidBytes(match, p1) {
                    return String.fromCharCode('0x' + p1);
            }));
        }

        // Helper: UTF-8 Safe Base64 Decode
        function b64DecodeUnicode(str) {
            return decodeURIComponent(atob(str).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        }

        // Helper: Detect and Render Buttons from Markdown Lists
        const detectAndRenderButtons = (element, rawText) => {
            // Skip button generation ONLY for complete dispute letters (has both markers)
            if (rawText.includes('== BEGIN LETTER ==') && rawText.includes('== END LETTER ==')) {
                return;
            }
            
            const options = [];
            const linesToRemove = [];
            
            // First, try to detect numbered lists: "1. Option" or "1. **Option**"
            const numberedRegex = /^(\d+)\.\s+(?:\*\*)?(.*?)(?:\*\*)?$/gm;
            let match;
            let safety = 0;
            
            while ((match = numberedRegex.exec(rawText)) !== null && safety < 20) {
                const label = match[2].trim();
                // Skip if it looks like a summary (contains colon) or is too long
                if (label.includes(':') || label.length === 0 || label.length > 150) {
                    safety++;
                    continue;
                }
                options.push({
                    number: match[1],
                    label: label
                });
                linesToRemove.push(match[0]);
                safety++;
            }
            
            // If no numbered lists found, try bullet lists: "• Option" or "- Option" or "* Option"
            if (options.length === 0) {
                const bulletRegex = /^[•\-\*]\s+(?:\*\*)?(.*?)(?:\*\*)?$/gm;
                let bulletMatch;
                let bulletIndex = 1;
                safety = 0;
                
                while ((bulletMatch = bulletRegex.exec(rawText)) !== null && safety < 20) {
                    const label = bulletMatch[1].trim();
                    // Skip if it looks like a summary (contains colon) or is too long
                    if (label.includes(':') || label.length === 0 || label.length > 150) {
                        safety++;
                        continue;
                    }
                    options.push({
                        number: bulletIndex.toString(),
                        label: label
                    });
                    linesToRemove.push(bulletMatch[0]);
                    bulletIndex++;
                    safety++;
                }
            }
            
            // Special case: If the message asks for Yes/No response, create Yes/No buttons
            if (options.length === 0 && /\b(yes or no|click yes or no|yes\/no|\(yes\/no\)|add another|would you like)\b/i.test(rawText)) {
                options.push(
                    { number: '1', label: 'Yes' },
                    { number: '2', label: 'No' }
                );
            }

            if (options.length > 0) {
                if (element.querySelector('.dynamic-btn-group')) return;

                // Remove the numbered list from the displayed text
                let cleanedHTML = element.innerHTML;
                linesToRemove.forEach(line => {
                    // Escape special regex characters and remove the line
                    const escapedLine = line.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    cleanedHTML = cleanedHTML.replace(new RegExp(`<li>${escapedLine}</li>`, 'g'), '');
                    cleanedHTML = cleanedHTML.replace(new RegExp(`${escapedLine}<br>`, 'g'), '');
                    cleanedHTML = cleanedHTML.replace(new RegExp(`${escapedLine}`, 'g'), '');
                });
                
                // Also remove the "Please enter the number..." instruction if present
                cleanedHTML = cleanedHTML.replace(/Please enter the number.*?choice\./gi, '');
                cleanedHTML = cleanedHTML.replace(/Please enter the number.*?dispute\./gi, '');
                
                // Clean up empty paragraphs and extra breaks
                cleanedHTML = cleanedHTML.replace(/<p>\s*<\/p>/g, '');
                cleanedHTML = cleanedHTML.replace(/<br>\s*<br>/g, '<br>');
                
                element.innerHTML = cleanedHTML;

                const btnContainer = document.createElement('div');
                btnContainer.className = 'd-flex flex-wrap gap-2 mt-3 dynamic-btn-group';
                
                options.forEach(opt => {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-outline-primary rounded-pill px-3';
                    btn.textContent = opt.label.replace(/[\*\[\]]/g, ''); 
                    btn.onclick = () => window.handleSendMessage(opt.label.replace(/[\*\[\]]/g, ''));
                    btnContainer.appendChild(btn);
                });

                element.appendChild(btnContainer);
            }
        };

        // Helper: Render Markdown
        const renderMarkdown = () => {
             document.querySelectorAll('.markdown-content').forEach(el => {
                const raw = el.dataset.md;
                if (!raw || el.dataset.rendered) return;

                let decoded = '';
                try {
                    decoded = b64DecodeUnicode(raw);
                } catch (e) {
                    console.error('Base64 decode error', e);
                    el.textContent = 'Error decoding message.';
                    return;
                }

                if (window.marked) {
                    try {
                        el.innerHTML = marked.parse(decoded);
                        el.dataset.rendered = "true"; 
                        detectAndRenderButtons(el, decoded);
                    } catch (e) {
                         console.error('Markdown parsing error', e);
                         el.textContent = decoded; 
                    }
                } else {
                    el.style.whiteSpace = 'pre-wrap';
                    el.textContent = decoded;
                    detectAndRenderButtons(el, decoded);
                }
            });
        };

        // Helper: Append Message to UI
        function appendMessage(role, content, timestamp = null, options = []) {
            const timeStr = timestamp 
                ? new Date(timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) 
                : new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            const wrapper = document.createElement('div');
            wrapper.className = `message-wrapper ${role} fade-in`; 
            
            if(role === 'assistant') {
                const avatar = document.createElement('div');
                avatar.className = 'chat-avatar';
                avatar.innerHTML = '<img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="width: 100%; height: 100%; object-fit: cover;">';
                wrapper.appendChild(avatar);
            }

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            
            if (role === 'assistant') {
                const mdContainer = document.createElement('div');
                mdContainer.className = 'markdown-content';
                try {
                    mdContainer.dataset.md = b64EncodeUnicode(content);
                } catch(e) {
                    console.error('Encoding error', e);
                    mdContainer.dataset.md = '';
                }
                bubble.appendChild(mdContainer);
                
                if (options && options.length > 0) {
                    const btnContainer = document.createElement('div');
                    btnContainer.className = 'd-flex flex-wrap gap-2 mt-3 quick-reply-buttons';
                    
                    options.forEach(option => {
                        const btn = document.createElement('button');
                        btn.className = 'btn btn-sm btn-primary rounded-pill px-3 py-2';
                        btn.style.fontSize = '0.875rem';
                        btn.textContent = option.label;
                        
                        if (option.value === 'View Disputes') {
                            btn.onclick = () => window.location.href = '/my-disputes';
                        } else {
                            btn.onclick = () => window.handleSendMessage(option.value || option.label);
                        }
                        
                        btnContainer.appendChild(btn);
                    });
                    
                    bubble.appendChild(btnContainer);
                }
            } else {
                bubble.textContent = content;
            }

            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = timeStr;
            bubble.appendChild(timeDiv);

            wrapper.appendChild(bubble);
            chatBox.insertBefore(wrapper, typingIndicator);
            
            if(role === 'assistant') renderMarkdown();
            scrollToBottom();
        }

        function showTyping(show) {
            typingIndicator.style.display = show ? 'flex' : 'none';
            if(show) scrollToBottom();
        }

        // UI: Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            sendBtn.disabled = this.value.trim().length === 0;
        });

        // UI: Enter to send
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if(!sendBtn.disabled) sendForm.requestSubmit();
            }
        });
        
        // Initial Scroll and Render
        scrollToBottom();
        renderMarkdown();

        // --- Core Chat Logic ---
        
        // Global send message function (for dynamic quick reply buttons)
        window.handleSendMessage = function(message) {
            if (!message) return;
            
            // 1. Add User Message UI
            appendMessage('user', message);
            
            // 2. Clear Input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendBtn.disabled = true;

            // 3. Show Typing Indicator
            showTyping(true);

            // 4. Send Request
            fetch("/credit-repair-bot", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ message })
            })
            .then(res => res.json())
            .then(data => {
                showTyping(false);
                
                // Check if this is a redirect response
                if (data.success && data.messages && data.messages.length > 0) {
                    const lastMessage = data.messages[data.messages.length - 1];
                    if (lastMessage.type === 'redirect' && lastMessage.redirect_url) {
                        // Show brief message then redirect
                        appendMessage('assistant', lastMessage.content, lastMessage.timestamp);
                        setTimeout(() => {
                            window.location.href = lastMessage.redirect_url;
                        }, 800);
                        return;
                    }
                }
                
                // Handle structured response - check if there are multiple new messages
                if (data.success && data.messages) {
                    // Find how many messages were already displayed
                    const currentMessageCount = chatBox.querySelectorAll('.message-wrapper').length;
                    const totalMessages = data.messages.length;
                    
                    // Display all NEW assistant messages (could be multiple, e.g., letter + success message)
                    for (let i = currentMessageCount; i < totalMessages; i++) {
                        const msg = data.messages[i];
                        if (msg.role === 'assistant') {
                            appendMessage('assistant', msg.content, msg.timestamp, msg.options || []);
                        }
                    }
                } else if (data.success && data.message) {
                    // Fallback: single message response
                    appendMessage('assistant', data.message, data.timestamp || null, data.options || []);
                }
            })
            .catch(err => {
                console.error(err);
                showTyping(false);
                appendMessage('assistant', '⚠️ Network error. Please try again.');
            });
        };
        
        sendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            handleSendMessage(message);
        });

        // --- Autocomplete Logic ---
        const autocompleteDropdown = document.getElementById('autocompleteDropdown');
        const autocompleteItems = autocompleteDropdown.querySelector('.autocomplete-items');
        let autocompleteEnabled = false;
        let autocompleteDebounce = null;
        let selectedIndex = -1;
        
        // Detect if the last AI message is asking for creditor name
        function shouldShowAutocomplete() {
            const messages = chatBox.querySelectorAll('.message-wrapper.assistant');
            if (messages.length === 0) return false;
            
            const lastMessage = messages[messages.length - 1];
            const text = lastMessage.textContent.toLowerCase();
            
            // Check if asking for creditor/collector name
            return text.includes('creditor') || text.includes('collector');
        }
        
        // Fetch creditor suggestions
        async function fetchCreditors(query) {
            if (query.length < 2) {
                hideAutocomplete();
                return;
            }
            
            try {
                const response = await fetch(`/api/creditors/search?q=${encodeURIComponent(query)}`);
                const creditors = await response.json();
                displayCreditors(creditors);
            } catch (error) {
                console.error('Autocomplete error:', error);
                hideAutocomplete();
            }
        }
        
        // Display creditor suggestions
        function displayCreditors(creditors) {
            autocompleteItems.innerHTML = '';
            selectedIndex = -1;
            
            if (creditors.length === 0) {
                hideAutocomplete();
                return;
            }
            
            creditors.forEach((creditor, index) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.dataset.index = index;
                item.dataset.name = creditor.name;
                
                item.innerHTML = `
                    <span class="autocomplete-item-name">${creditor.name}</span>
                    <span class="autocomplete-item-type">${creditor.type}</span>
                `;
                
                item.addEventListener('click', () => {
                    selectCreditor(creditor.name);
                });
                
                autocompleteItems.appendChild(item);
            });
            
            showAutocomplete();
        }
        
        // Select a creditor
        function selectCreditor(name) {
            messageInput.value = name;
            hideAutocomplete();
            messageInput.focus();
        }
        
        // Show/hide autocomplete
        function showAutocomplete() {
            autocompleteDropdown.style.display = 'block';
        }
        
        function hideAutocomplete() {
            autocompleteDropdown.style.display = 'none';
            selectedIndex = -1;
        }
        
        // Handle keyboard navigation
        function handleAutocompleteKeyboard(e) {
            const items = autocompleteItems.querySelectorAll('.autocomplete-item');
            
            if (items.length === 0) return;
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(items);
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                const selectedItem = items[selectedIndex];
                selectCreditor(selectedItem.dataset.name);
            } else if (e.key === 'Escape') {
                hideAutocomplete();
            }
        }
        
        function updateSelection(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                }
            });
        }
        
        // Listen for input changes
        messageInput.addEventListener('input', function() {
            // Check if autocomplete should be enabled
            autocompleteEnabled = shouldShowAutocomplete();
            
            if (!autocompleteEnabled) {
                hideAutocomplete();
                return;
            }
            
            const query = this.value.trim();
            
            // Debounce the API call (reduced to 150ms for faster response)
            clearTimeout(autocompleteDebounce);
            autocompleteDebounce = setTimeout(() => {
                fetchCreditors(query);
            }, 150);
        });
        
        // Keyboard navigation
        messageInput.addEventListener('keydown', function(e) {
            if (autocompleteDropdown.style.display === 'block') {
                handleAutocompleteKeyboard(e);
            }
        });
        
        // Close autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!autocompleteDropdown.contains(e.target) && e.target !== messageInput) {
                hideAutocomplete();
            }
        });

        // --- Reset Logic ---
        const resetForm = document.getElementById('resetForm');
        const resetBtn = document.getElementById('resetBtn');
        const resetModal = new bootstrap.Modal(document.getElementById('resetModal'));
        const confirmResetBtn = document.getElementById('confirmResetBtn');
        
        // Show modal when reset button is clicked
        resetBtn.addEventListener('click', function() {
            resetModal.show();
        });
        
        // Submit form when user confirms
        confirmResetBtn.addEventListener('click', function() {
            resetModal.hide();
            resetForm.submit();
        });
    });
</script>
@endpush

