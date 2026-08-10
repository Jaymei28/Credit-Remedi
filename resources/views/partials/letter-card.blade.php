<div style="border: 1px solid #E4E2EB; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px; background-color: {{ $locked ? '#F8F7FA' : '#FFFFFF' }}; position: relative; transition: all 0.2s ease;">
    
    <!-- Top Row: Bureau & Badges -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 18px;">
                @if($letter->credit_bureau === 'Equifax')
                    🔴
                @elseif($letter->credit_bureau === 'Experian')
                    🔵
                @else
                    🟢
                @endif
            </span>
            <span style="font-size: 14px; font-weight: 700; color: #15141C;">
                {{ $letter->credit_bureau }}
            </span>
        </div>
        
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <!-- Sent Status -->
            @if($letter->sent)
                <span style="background-color: rgba(15,169,156,0.1); color: #0FA99C; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    ✓ Mailed
                </span>
            @else
                <span style="background-color: rgba(224,85,63,0.1); color: #E0553F; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    ● Ready to Mail
                </span>
            @endif

            <!-- Vault Status -->
            @if($letter->posted_1)
                <span style="background-color: rgba(92,88,107,0.1); color: #5C586B; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                    🔒 Saved in Vault
                </span>
            @endif
        </div>
    </div>

    <!-- Middle: Creditor Info -->
    <div>
        <h4 style="font-size: 14px; font-weight: 600; color: #15141C; margin: 0 0 2px;">
            {{ $letter->creditor_name }}
        </h4>
        <p style="font-size: 12.5px; color: #8B879A; margin: 0;">
            Account: {{ $letter->account_number }}
        </p>
    </div>

    <!-- Bottom: Action Buttons -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; border-top: 1px solid #F0EFF4; padding-top: 12px; margin-top: 2px;">
        @if(!$locked)
            <!-- Download PDF -->
            <a href="{{ route('disputes.downloadPdf', $letter->id) }}" style="background-color: #0FA99C; color: white; border: none; border-radius: 6px; padding: 8px 14px; font-size: 12.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.15s;">
                📥 Download PDF
            </a>
            
            <!-- Edit Button using data attribute to avoid JSON quote conflicts -->
            <button onclick="openEditModal({{ $letter->id }})" id="edit-btn-{{ $letter->id }}" data-content="{{ $letter->letter_content }}" style="background-color: #FFFFFF; color: #15141C; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 14px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                ✏️ Edit Letter
            </button>

            <!-- Toggle Sent -->
            <form action="{{ route('disputes.updateSent', $letter->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                @if($letter->sent)
                    <!-- Active Green state -->
                    <button type="submit" style="background-color: #E6F7F4; color: #0FA99C; border: 1px solid #0FA99C; border-radius: 6px; padding: 8px 14px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                        ↩ Mark Unsent
                    </button>
                @else
                    <!-- Inactive Red/Alert state -->
                    <button type="submit" style="background-color: #FFF0ED; color: #E0553F; border: 1px solid #E0553F; border-radius: 6px; padding: 8px 14px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                        ✉ Mark as Mailed
                    </button>
                @endif
            </form>

            <!-- Toggle Vault -->
            <form action="{{ route('disputes.togglePost', $letter->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" style="background-color: #FFFFFF; color: #5C586B; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 14px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                    {{ $letter->posted_1 ? '🔓 Remove Vault' : '🔒 Save to Vault' }}
                </button>
            </form>
        @else
            <!-- Locked State Actions -->
            <span style="font-size: 12px; color: #8B879A; display: inline-flex; align-items: center; gap: 6px;">
                🔒 Locked — complete Phase {{ $letter->phase - 1 }} disputes first to unlock.
            </span>
        @endif
    </div>

</div>
