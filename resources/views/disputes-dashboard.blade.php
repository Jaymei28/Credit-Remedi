@extends('layouts.app')

@section('title', 'Phased Dispute Board - Ally AI')

@section('content')
<div style="background-color: #F2F0F7; min-height: 92vh; padding: 40px 16px; font-family: 'Inter', sans-serif;">
    <div style="max-width: 1040px; margin: 0 auto;">
        
        <!-- Meet Ally Coaching Header -->
        <div style="background: #0B0B10; color: white; border-radius: 20px; padding: 30px; display: flex; flex-direction: column; gap: 24px; margin-bottom: 32px; box-shadow: 0 8px 30px rgba(0,0,0,0.05); position: relative; overflow: hidden;">
            <div style="position: absolute; right: -50px; bottom: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(34,211,197,0.15) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>
            
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background-color: #fff; border: 3px solid #22D3C5; padding: 4px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(34,211,197,0.3);">
                    <img src="{{ asset('AllyAI.png') }}" alt="Ally AI" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div>
                    <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: clamp(22px, 4vw, 30px); margin: 0 0 4px; font-weight: 700;">
                        Meet Ally: Your Credit Game Plan
                    </h1>
                    <p style="font-size: 13.5px; color: #8B879A; margin: 0;">
                        Let's tackle your disputes in rounds to weaken reporting credibility and maximize deletions.
                    </p>
                </div>
            </div>

            <!-- Audit Metrics Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; border-top: 1px solid #1F1E29; padding-top: 24px;">
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #8B879A; letter-spacing: 0.05em; margin-bottom: 4px;">Audit Score</div>
                    <div style="display: flex; align-items: baseline; gap: 8px;">
                        <span style="font-size: 28px; font-weight: 700; color: #22D3C5;">{{ $auditScore }}</span>
                        <span style="font-size: 14px; color: #8B879A;">/ 98</span>
                    </div>
                </div>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #8B879A; letter-spacing: 0.05em; margin-bottom: 4px;">Current Phase</div>
                    <div style="font-size: 16px; font-weight: 700; color: {{ $scoreLabel[1] }};">{{ $scoreLabel[0] }}</div>
                </div>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #8B879A; letter-spacing: 0.05em; margin-bottom: 4px;">Profile Complexity</div>
                    <div style="font-size: 16px; font-weight: 700; text-transform: capitalize; color: #white;">
                        @if($complexity === 'high')
                            🔴 High Complexity
                        @elseif($complexity === 'medium')
                            🟡 Medium Complexity
                        @else
                            🟢 Low Complexity
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- How it Works Guide -->
        <div style="background: white; border: 1px solid #E4E2EB; border-radius: 16px; padding: 24px; margin-bottom: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
            <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 18px; color: #15141C; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                📋 How to Use Your Game Plan
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <div style="display: flex; gap: 12px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(15,169,156,0.1); color: #0FA99C; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">1</div>
                    <div>
                        <h4 style="font-size: 13.5px; font-weight: 600; color: #15141C; margin: 0 0 4px;">Download & Mail</h4>
                        <p style="font-size: 12px; color: #5C586B; margin: 0; line-height: 1.5;">Click <strong>Download PDF</strong> for each active letter, print them, and mail them to the credit bureaus.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(15,169,156,0.1); color: #0FA99C; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">2</div>
                    <div>
                        <h4 style="font-size: 13.5px; font-weight: 600; color: #15141C; margin: 0 0 4px;">Mark as Mailed</h4>
                        <p style="font-size: 12px; color: #5C586B; margin: 0; line-height: 1.5;">After mailing, click <strong>Mark as Mailed</strong>. This tracks the dates and unlocks the next phase dynamically.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(15,169,156,0.1); color: #0FA99C; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">3</div>
                    <div>
                        <h4 style="font-size: 13.5px; font-weight: 600; color: #15141C; margin: 0 0 4px;">Save to Vault</h4>
                        <p style="font-size: 12px; color: #5C586B; margin: 0; line-height: 1.5;">Click <strong>Save to Vault</strong> to archive the letter. You can view all archived letters in the <strong>Disputes</strong> tab.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Strategy Advice Drawer -->
        @if(!empty($findings))
            <div style="background: white; border: 1px solid #E4E2EB; border-radius: 16px; padding: 24px; margin-bottom: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
                <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 18px; color: #15141C; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                    💡 Strategic Insights from Ally
                </h3>
                <div style="display: grid; gap: 14px; max-height: 250px; overflow-y: auto; padding-right: 8px;">
                    @foreach($findings as $finding)
                        <div style="border-left: 3px solid {{ $finding['tagColor'] }}; padding-left: 14px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 700; color: {{ $finding['tagColor'] }}; text-transform: uppercase; letter-spacing: 0.03em;">{{ $finding['tag'] }}</span>
                                <span style="font-size: 11px; color: #8B879A;">• {{ $finding['timeline'] }}</span>
                            </div>
                            <h4 style="font-size: 13.5px; font-weight: 600; color: #15141C; margin: 0 0 2px;">{{ $finding['title'] }}</h4>
                            <p style="font-size: 12.5px; color: #5C586B; margin: 0; line-height: 1.5;">{{ $finding['why'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Phased Dispute timeline/lanes -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
            
            <!-- Phase 1 Lane -->
            <div style="background: white; border: 1px solid #E4E2EB; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); overflow: hidden;">
                <div style="background-color: #0FA99C; color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 700; margin: 0;">⚡ Phase 1: Clean & Challenge</h2>
                        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 2px 0 0;">Personal Info corrections, Inquiries, and Collection accounts.</p>
                    </div>
                    <span style="background: rgba(255,255,255,0.15); font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase;">Active Phase</span>
                </div>
                
                <div style="padding: 24px; display: grid; gap: 16px;">
                    @php $p1Letters = $phases[1] ?? collect(); @endphp
                    @forelse($p1Letters as $letter)
                        @include('partials.letter-card', ['letter' => $letter, 'locked' => false])
                    @empty
                        <div style="text-align: center; padding: 30px; color: #8B879A; font-size: 14px; border: 1px dashed #E4E2EB; border-radius: 12px;">
                            No Phase 1 letters generated.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Phase 2 Lane -->
            @php 
                $p1Done = ($p1Letters->isEmpty() || $p1Letters->where('sent', false)->isEmpty());
            @endphp
            <div id="phase-2-container" style="background: white; border: 1px solid #E4E2EB; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); overflow: hidden; opacity: {{ $p1Done ? '1' : '0.65' }}; transition: all 0.3s ease;">
                <div style="background-color: #15141C; color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 700; margin: 0;">🛠️ Phase 2: Core Battle</h2>
                        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 2px 0 0;">Charge-offs and Repossession accounts.</p>
                    </div>
                    <span id="phase-2-status" style="background: {{ $p1Done ? 'rgba(34,211,197,0.15)' : 'rgba(224,85,63,0.15)' }}; color: {{ $p1Done ? '#22D3C5' : '#E0553F' }}; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; border: {{ $p1Done ? 'none' : '1px solid #E0553F' }}; transition: all 0.3s ease;">
                        {{ $p1Done ? 'Active' : 'Locked' }}
                    </span>
                </div>

                <div id="phase-2-warning" style="display: {{ !$p1Done ? 'flex' : 'none' }}; background: #FFF9F3; border-bottom: 1px solid #FFE0C5; padding: 12px 24px; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⚠️</span>
                    <span style="font-size: 12.5px; color: #D69A2D; font-weight: 500;">
                        <strong>Ally advises:</strong> Complete Phase 1 disputes (mail all Phase 1 letters) before unlocking Phase 2. This isolates charge-offs for higher deletion odds.
                    </span>
                </div>

                <div style="padding: 24px; display: grid; gap: 16px;">
                    @php $p2Letters = $phases[2] ?? collect(); @endphp
                    @forelse($p2Letters as $letter)
                        @include('partials.letter-card', ['letter' => $letter, 'locked' => !$p1Done])
                    @empty
                        <div style="text-align: center; padding: 30px; color: #8B879A; font-size: 14px; border: 1px dashed #E4E2EB; border-radius: 12px;">
                            No Phase 2 letters generated.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Phase 3 Lane -->
            @php 
                $p2Done = $p1Done && ($p2Letters->isEmpty() || $p2Letters->where('sent', false)->isEmpty());
            @endphp
            <div id="phase-3-container" style="background: white; border: 1px solid #E4E2EB; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); overflow: hidden; opacity: {{ $p2Done ? '1' : '0.65' }}; transition: all 0.3s ease;">
                <div style="background-color: #15141C; color: white; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 700; margin: 0;">✨ Phase 3: Goodwill & Polish</h2>
                        <p style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 2px 0 0;">Late Payment history corrections and Goodwill campaigns.</p>
                    </div>
                    <span id="phase-3-status" style="background: {{ $p2Done ? 'rgba(34,211,197,0.15)' : 'rgba(224,85,63,0.15)' }}; color: {{ $p2Done ? '#22D3C5' : '#E0553F' }}; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; border: {{ $p2Done ? 'none' : '1px solid #E0553F' }}; transition: all 0.3s ease;">
                        {{ $p2Done ? 'Active' : 'Locked' }}
                    </span>
                </div>

                <div id="phase-3-warning" style="display: {{ !$p2Done ? 'flex' : 'none' }}; background: #FFF9F3; border-bottom: 1px solid #FFE0C5; padding: 12px 24px; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⚠️</span>
                    <span style="font-size: 12.5px; color: #D69A2D; font-weight: 500;">
                        <strong>Ally advises:</strong> Finish your Phase 2 disputes before starting late payment adjustments. This keeps your dispute history credible.
                    </span>
                </div>

                <div style="padding: 24px; display: grid; gap: 16px;">
                    @php $p3Letters = $phases[3] ?? collect(); @endphp
                    @forelse($p3Letters as $letter)
                        @include('partials.letter-card', ['letter' => $letter, 'locked' => !$p2Done])
                    @empty
                        <div style="text-align: center; padding: 30px; color: #8B879A; font-size: 14px; border: 1px dashed #E4E2EB; border-radius: 12px;">
                            No Phase 3 letters generated.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Edit Letter Modal -->
<div id="edit-letter-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 640px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="background-color: #15141C; color: white; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 16px; font-weight: 600; margin: 0;">✍️ Edit Dispute Letter</h3>
            <button onclick="closeEditModal()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <form id="edit-letter-form" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            @method('PATCH')
            <div style="padding: 24px; flex: 1; overflow-y: auto;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 8px;">Letter Content</label>
                <textarea id="modal-letter-content" name="letter_content" style="width: 100%; height: 400px; border: 1px solid #E4E2EB; border-radius: 8px; padding: 14px; font-family: monospace; font-size: 13px; color: #15141C; outline: none; resize: vertical;" required></textarea>
            </div>
            <div style="background-color: #F8F7FA; border-top: 1px solid #E4E2EB; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeEditModal()" style="background: white; border: 1px solid #E4E2EB; border-radius: 8px; padding: 8px 16px; font-size: 13.5px; color: #5C586B; cursor: pointer;">Cancel</button>
                <button type="submit" style="background-color: #0FA99C; color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 13.5px; font-weight: 600; cursor: pointer;">Save Letter</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(letterId) {
        const btn = document.getElementById('edit-btn-' + letterId);
        const content = btn.getAttribute('data-content');
        const form = document.getElementById('edit-letter-form');
        form.action = `/disputes/${letterId}/update-letter`;
        document.getElementById('modal-letter-content').value = content;
        document.getElementById('edit-letter-modal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('edit-letter-modal').style.display = 'none';
    }

    function checkPhaseLocks() {
        // Count unsent letters in Phase 1
        const unsentP1 = document.querySelectorAll('.letter-card[data-phase="1"][data-sent="false"]').length;
        // Count unsent letters in Phase 2
        const unsentP2 = document.querySelectorAll('.letter-card[data-phase="2"][data-sent="false"]').length;

        // Determine locks
        const p1Done = (unsentP1 === 0);
        const p2Done = (p1Done && unsentP2 === 0);

        // Update Phase 2 UI
        const phase2Container = document.getElementById('phase-2-container');
        const phase2Status = document.getElementById('phase-2-status');
        const phase2Warning = document.getElementById('phase-2-warning');

        if (p1Done) {
            phase2Container.style.opacity = '1';
            phase2Status.innerText = 'Active';
            phase2Status.style.background = 'rgba(34,211,197,0.15)';
            phase2Status.style.color = '#22D3C5';
            phase2Status.style.border = 'none';
            phase2Warning.style.display = 'none';

            // Unlock letter cards in Phase 2
            document.querySelectorAll('.letter-card[data-phase="2"]').forEach(card => {
                card.style.backgroundColor = 'transparent';
                card.querySelector('.unlocked-actions').style.display = 'inline-flex';
                card.querySelector('.locked-placeholder').style.display = 'none';
            });
        } else {
            phase2Container.style.opacity = '0.65';
            phase2Status.innerText = 'Locked';
            phase2Status.style.background = 'rgba(224,85,63,0.15)';
            phase2Status.style.color = '#E0553F';
            phase2Status.style.border = '1px solid #E0553F';
            phase2Warning.style.display = 'flex';

            // Lock letter cards in Phase 2
            document.querySelectorAll('.letter-card[data-phase="2"]').forEach(card => {
                card.style.backgroundColor = '#F8F7FA';
                card.querySelector('.unlocked-actions').style.display = 'none';
                card.querySelector('.locked-placeholder').style.display = 'inline-flex';
            });
        }

        // Update Phase 3 UI
        const phase3Container = document.getElementById('phase-3-container');
        const phase3Status = document.getElementById('phase-3-status');
        const phase3Warning = document.getElementById('phase-3-warning');

        if (p2Done) {
            phase3Container.style.opacity = '1';
            phase3Status.innerText = 'Active';
            phase3Status.style.background = 'rgba(34,211,197,0.15)';
            phase3Status.style.color = '#22D3C5';
            phase3Status.style.border = 'none';
            phase3Warning.style.display = 'none';

            // Unlock letter cards in Phase 3
            document.querySelectorAll('.letter-card[data-phase="3"]').forEach(card => {
                card.style.backgroundColor = 'transparent';
                card.querySelector('.unlocked-actions').style.display = 'inline-flex';
                card.querySelector('.locked-placeholder').style.display = 'none';
            });
        } else {
            phase3Container.style.opacity = '0.65';
            phase3Status.innerText = 'Locked';
            phase3Status.style.background = 'rgba(224,85,63,0.15)';
            phase3Status.style.color = '#E0553F';
            phase3Status.style.border = '1px solid #E0553F';
            phase3Warning.style.display = 'flex';

            // Lock letter cards in Phase 3
            document.querySelectorAll('.letter-card[data-phase="3"]').forEach(card => {
                card.style.backgroundColor = '#F8F7FA';
                card.querySelector('.unlocked-actions').style.display = 'none';
                card.querySelector('.locked-placeholder').style.display = 'inline-flex';
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Intercept sent form submits (Mark as Mailed)
        document.querySelectorAll('.toggle-sent-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formAction = this.action;
                const btn = this.querySelector('.sent-btn');
                const card = this.closest('.letter-card');
                const sentBadge = card.querySelector('.sent-badge');

                // Mute button during ajax
                btn.disabled = true;
                btn.style.opacity = '0.7';

                fetch(formAction, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(this)
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.style.opacity = '1';

                    if (data.success) {
                        if (data.sent) {
                            // Green solid Mailed state
                            btn.style.backgroundColor = '#0FA99C';
                            btn.style.color = '#FFFFFF';
                            btn.style.borderColor = '#0FA99C';
                            btn.style.fontWeight = '700';
                            btn.innerText = '✓ Mailed!';
                            
                            sentBadge.style.backgroundColor = 'rgba(15,169,156,0.1)';
                            sentBadge.style.color = '#0FA99C';
                            sentBadge.innerText = '✓ Mailed';
                            
                            card.setAttribute('data-sent', 'true');
                        } else {
                            // Inactive state
                            btn.style.backgroundColor = '#FFFFFF';
                            btn.style.color = '#5C586B';
                            btn.style.borderColor = '#E4E2EB';
                            btn.style.fontWeight = '600';
                            btn.innerText = '✉ Mark as Mailed';
                            
                            sentBadge.style.backgroundColor = 'rgba(224,85,63,0.1)';
                            sentBadge.style.color = '#E0553F';
                            sentBadge.innerText = '● Ready to Mail';
                            
                            card.setAttribute('data-sent', 'false');
                        }
                        
                        // Check locks immediately in real time
                        checkPhaseLocks();
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    console.error('Error toggling sent status:', error);
                });
            });
        });

        // Intercept vault form submits (Save to Vault)
        document.querySelectorAll('.toggle-vault-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formAction = this.action;
                const btn = this.querySelector('.vault-btn');
                const card = this.closest('.letter-card');
                const vaultBadge = card.querySelector('.vault-badge');

                btn.disabled = true;
                btn.style.opacity = '0.7';

                fetch(formAction, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(this)
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.style.opacity = '1';

                    if (data.success) {
                        if (data.posted_1) {
                            btn.style.backgroundColor = '#E6F7F4';
                            btn.style.color = '#0FA99C';
                            btn.style.borderColor = '#0FA99C';
                            btn.innerText = '🔒 Saved in Vault';
                            
                            vaultBadge.style.display = 'inline-flex';
                        } else {
                            btn.style.backgroundColor = '#FFFFFF';
                            btn.style.color = '#5C586B';
                            btn.style.borderColor = '#E4E2EB';
                            btn.innerText = '🔒 Save to Vault';
                            
                            vaultBadge.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    console.error('Error toggling vault status:', error);
                });
            });
        });
    });
</script>
@endsection
