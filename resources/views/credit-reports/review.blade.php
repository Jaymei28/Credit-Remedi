@extends('layouts.app')

@section('title', 'Review & Approve Report Data')

@section('content')
<div style="background-color: #F2F0F7; min-height: 92vh; padding: 40px 16px; font-family: 'Inter', sans-serif;">
    <div style="max-width: 760px; margin: 0 auto;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="display: inline-block; background-color: #0FA99C; color: white; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                Review Step
            </div>
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: clamp(24px, 5vw, 36px); color: #15141C; margin: 0 0 10px;">
                Verify Your Account Details
            </h1>
            <p style="font-size: 14.5px; color: #8B879A; max-width: 500px; margin: 0 auto; line-height: 1.5;">
                We've extracted the following details. Please review them and edit any incorrect account numbers or balances before generating your dispute letters.
            </p>
        </div>

        <form action="{{ route('credit-reports.saveReview') }}" method="POST">
            @csrf
            
            <!-- Section 1: Personal Info Card -->
            <div style="background: white; border: 1px solid #E4E2EB; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px;">
                <div style="background-color: #15141C; color: white; padding: 18px 24px;">
                    <h2 style="font-size: 16px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px;">
                        👤 Personal Information
                    </h2>
                </div>
                <div style="padding: 24px; display: grid; gap: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 6px;">First Name</label>
                            <input type="text" name="personal_info[first_name]" value="{{ $parsedData['personal_info']['first_name'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; color: #15141C;" required>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 6px;">Last Name</label>
                            <input type="text" name="personal_info[last_name]" value="{{ $parsedData['personal_info']['last_name'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; color: #15141C;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 6px;">Current Address</label>
                            <input type="text" name="personal_info[current_address]" value="{{ $parsedData['personal_info']['current_address'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; color: #15141C;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 6px;">Date of Birth</label>
                            <input type="text" name="personal_info[date_of_birth]" value="{{ $parsedData['personal_info']['date_of_birth'] ?? '' }}" placeholder="MM/DD/YYYY" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; color: #15141C;">
                        </div>
                    </div>

                    <div style="border-top: 1px solid #F0EFF4; pt: 16px; margin-top: 8px; padding-top: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #3A3844; margin-bottom: 10px;">Select variations found on report:</label>
                        <div style="display: grid; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #15141C; cursor: pointer;">
                                <input type="checkbox" name="personal_info[identifiers][]" value="namevar" {{ in_array('namevar', $parsedData['personal_info']['identifiers'] ?? []) ? 'checked' : '' }}>
                                Multiple name variations reporting
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #15141C; cursor: pointer;">
                                <input type="checkbox" name="personal_info[identifiers][]" value="oldaddr" {{ in_array('oldaddr', $parsedData['personal_info']['identifiers'] ?? []) ? 'checked' : '' }}>
                                Outdated or incorrect addresses reporting
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #15141C; cursor: pointer;">
                                <input type="checkbox" name="personal_info[identifiers][]" value="otherid" {{ in_array('otherid', $parsedData['personal_info']['identifiers'] ?? []) ? 'checked' : '' }}>
                                Inaccurate employer names listed
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Accounts Card List -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding: 0 4px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin: 0; display: flex; align-items: center; gap: 8px;">
                        💳 Credit Accounts ({{ count($parsedData['accounts'] ?? []) }})
                    </h3>
                    <button type="button" onclick="addAccountRow()" style="background: none; border: none; font-size: 13px; font-weight: 600; color: #0FA99C; cursor: pointer; text-decoration: underline; padding: 0;">
                        + Add Account
                    </button>
                </div>

                <div id="accounts-container" style="display: grid; gap: 16px;">
                    @forelse($parsedData['accounts'] ?? [] as $index => $acc)
                        <div class="account-card" style="background: white; border: 1px solid #E4E2EB; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.01); overflow: hidden;">
                            <div style="background-color: #F8F7FA; border-bottom: 1px solid #E4E2EB; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 12px; font-weight: 700; color: #8B879A; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Account #<span class="row-num">{{ $index + 1 }}</span>
                                </span>
                                <button type="button" onclick="removeCard(this)" style="background: none; border: none; font-size: 12px; color: #E0553F; cursor: pointer; text-decoration: underline; padding: 0;">
                                    Remove
                                </button>
                            </div>
                            <div style="padding: 20px; display: grid; gap: 14px;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                    <div>
                                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Creditor Name</label>
                                        <input type="text" name="accounts[{{ $index }}][creditor_name]" value="{{ $acc['creditor_name'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Account Type</label>
                                        <select name="accounts[{{ $index }}][account_type]" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C; background: white;">
                                            <option value="collection" {{ ($acc['account_type'] ?? '') === 'collection' ? 'selected' : '' }}>Collection</option>
                                            <option value="charge-off" {{ ($acc['account_type'] ?? '') === 'charge-off' ? 'selected' : '' }}>Charge-off</option>
                                            <option value="repossession" {{ ($acc['account_type'] ?? '') === 'repossession' ? 'selected' : '' }}>Repossession</option>
                                            <option value="late payment" {{ ($acc['account_type'] ?? '') === 'late payment' ? 'selected' : '' }}>Late Payment</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                    <div>
                                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Account Number</label>
                                        <input type="text" name="accounts[{{ $index }}][account_number]" value="{{ $acc['account_number'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Reported Balance ($)</label>
                                        <input type="number" step="0.01" name="accounts[{{ $index }}][balance]" value="{{ $acc['balance'] ?? 0 }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr; gap: 14px;">
                                    <div>
                                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Credit Bureau(s)</label>
                                        <input type="text" name="accounts[{{ $index }}][bureau]" value="{{ $acc['bureau'] ?? 'All' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" placeholder="Equifax, Experian, TransUnion" required>
                                    </div>
                                </div>

                                <div>
                                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Dispute Reason</label>
                                    <textarea name="accounts[{{ $index }}][dispute_reason]" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13px; color: #15141C; height: 60px; outline: none; resize: vertical;" required>{{ $acc['dispute_reason'] ?? 'Incorrect information, please verify validity and accuracy of reporting details.' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="no-accounts-msg" style="background: white; border: 1px dashed #E4E2EB; border-radius: 12px; padding: 30px; text-align: center; color: #8B879A;">
                            No derogatory accounts found. Click "+ Add Account" to insert one manually.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Section 3: Inquiries Card List -->
            <div style="margin-bottom: 32px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding: 0 4px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin: 0; display: flex; align-items: center; gap: 8px;">
                        🔎 Hard Inquiries ({{ count($parsedData['inquiries'] ?? []) }})
                    </h3>
                    <button type="button" onclick="addInquiryRow()" style="background: none; border: none; font-size: 13px; font-weight: 600; color: #0FA99C; cursor: pointer; text-decoration: underline; padding: 0;">
                        + Add Inquiry
                    </button>
                </div>

                <div id="inquiries-container" style="display: grid; gap: 14px;">
                    @forelse($parsedData['inquiries'] ?? [] as $index => $inq)
                        <div class="inquiry-card" style="background: white; border: 1px solid #E4E2EB; border-radius: 12px; padding: 16px; display: grid; gap: 12px; position: relative;">
                            <button type="button" onclick="removeCard(this)" style="position: absolute; right: 16px; top: 16px; background: none; border: none; font-size: 11.5px; color: #E0553F; cursor: pointer; text-decoration: underline; padding: 0;">
                                Remove
                            </button>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; width: calc(100% - 60px);">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Creditor Name</label>
                                    <input type="text" name="inquiries[{{ $index }}][creditor_name]" value="{{ $inq['creditor_name'] ?? '' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Inquiry Date</label>
                                    <input type="text" name="inquiries[{{ $index }}][inquiry_date]" value="{{ $inq['inquiry_date'] ?? '' }}" placeholder="MM/DD/YYYY" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Credit Bureau(s)</label>
                                    <input type="text" name="inquiries[{{ $index }}][bureau]" value="{{ $inq['bureau'] ?? 'All' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Dispute Reason</label>
                                <input type="text" name="inquiries[{{ $index }}][dispute_reason]" value="{{ $inq['dispute_reason'] ?? 'No permissible purpose / unauthorized inquiry.' }}" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                            </div>
                        </div>
                    @empty
                        <div id="no-inquiries-msg" style="background: white; border: 1px dashed #E4E2EB; border-radius: 12px; padding: 24px; text-align: center; color: #8B879A;">
                            No hard inquiries found. Click "+ Add Inquiry" to insert one manually.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Submit Panel -->
            <div style="text-align: center; margin-top: 16px;">
                <button type="submit" style="font-family: 'Inter', sans-serif; font-size: 14.5px; font-weight: 700; letter-spacing: 0.02em; padding: 18px 40px; border-radius: 10px; cursor: pointer; background-color: #0FA99C; color: white; border: none; width: 100%; max-width: 440px; box-shadow: 0 4px 14px rgba(15,169,156,0.3); transition: all 0.2s ease;">
                    CONFIRM & GENERATE MY GAME PLAN →
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    let accountIndex = {{ count($parsedData['accounts'] ?? []) }};
    let inquiryIndex = {{ count($parsedData['inquiries'] ?? []) }};

    function addAccountRow() {
        const container = document.getElementById('accounts-container');
        const noMsg = document.getElementById('no-accounts-msg');
        if (noMsg) noMsg.remove();

        const card = document.createElement('div');
        card.className = 'account-card';
        card.style = "background: white; border: 1px solid #E4E2EB; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.01); overflow: hidden; margin-top: 10px;";
        card.innerHTML = `
            <div style="background-color: #F8F7FA; border-bottom: 1px solid #E4E2EB; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 12px; font-weight: 700; color: #8B879A; text-transform: uppercase; letter-spacing: 0.05em;">
                    Account #<span class="row-num">${accountIndex + 1}</span>
                </span>
                <button type="button" onclick="removeCard(this)" style="background: none; border: none; font-size: 12px; color: #E0553F; cursor: pointer; text-decoration: underline; padding: 0;">
                    Remove
                </button>
            </div>
            <div style="padding: 20px; display: grid; gap: 14px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Creditor Name</label>
                        <input type="text" name="accounts[${accountIndex}][creditor_name]" placeholder="e.g. Credit One Bank" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Account Type</label>
                        <select name="accounts[${accountIndex}][account_type]" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C; background: white;">
                            <option value="collection">Collection</option>
                            <option value="charge-off">Charge-off</option>
                            <option value="repossession">Repossession</option>
                            <option value="late payment">Late Payment</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Account Number</label>
                        <input type="text" name="accounts[${accountIndex}][account_number]" placeholder="e.g. 123456XXXX" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Reported Balance ($)</label>
                        <input type="number" step="0.01" name="accounts[${accountIndex}][balance]" value="0" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Credit Bureau(s)</label>
                        <input type="text" name="accounts[${accountIndex}][bureau]" value="All" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: #15141C;" required>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Dispute Reason</label>
                    <textarea name="accounts[${accountIndex}][dispute_reason]" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 8px 12px; font-size: 13px; color: #15141C; height: 60px; outline: none; resize: vertical;" required>Incorrect information, please verify validity and accuracy of reporting details.</textarea>
                </div>
            </div>
        `;
        container.appendChild(card);
        accountIndex++;
        renumberAccounts();
    }

    function addInquiryRow() {
        const container = document.getElementById('inquiries-container');
        const noMsg = document.getElementById('no-inquiries-msg');
        if (noMsg) noMsg.remove();

        const card = document.createElement('div');
        card.className = 'inquiry-card';
        card.style = "background: white; border: 1px solid #E4E2EB; border-radius: 12px; padding: 16px; display: grid; gap: 12px; position: relative; margin-top: 10px;";
        card.innerHTML = `
            <button type="button" onclick="removeCard(this)" style="position: absolute; right: 16px; top: 16px; background: none; border: none; font-size: 11.5px; color: #E0553F; cursor: pointer; text-decoration: underline; padding: 0;">
                Remove
            </button>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; width: calc(100% - 60px);">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Creditor Name</label>
                    <input type="text" name="inquiries[${inquiryIndex}][creditor_name]" placeholder="e.g. Chase Bank" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Inquiry Date</label>
                    <input type="text" name="inquiries[${inquiryIndex}][inquiry_date]" placeholder="MM/DD/YYYY" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Credit Bureau(s)</label>
                    <input type="text" name="inquiries[${inquiryIndex}][bureau]" value="All" style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 11px; font-weight: 600; color: #3A3844; margin-bottom: 4px;">Dispute Reason</label>
                <input type="text" name="inquiries[${inquiryIndex}][dispute_reason]" value="No permissible purpose / unauthorized inquiry." style="width: 100%; border: 1px solid #E4E2EB; border-radius: 6px; padding: 6px 10px; font-size: 13px; color: #15141C;" required>
            </div>
        `;
        container.appendChild(card);
        inquiryIndex++;
    }

    function removeCard(btn) {
        const card = btn.closest('.account-card, .inquiry-card');
        card.remove();
        renumberAccounts();
    }

    function renumberAccounts() {
        const labels = document.querySelectorAll('.account-card .row-num');
        labels.forEach((lbl, idx) => {
            lbl.textContent = idx + 1;
        });
    }
</script>
@endsection
