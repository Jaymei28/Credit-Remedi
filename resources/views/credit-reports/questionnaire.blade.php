@extends('layouts.app')

@section('title', 'Manual Assessment Questionnaire - Ally AI')

@section('content')
<div style="background-color: #F2F0F7; min-height: 92vh; padding: 40px 16px; font-family: 'Inter', sans-serif;">
    <div style="max-width: 640px; margin: 0 auto; background: white; border: 1px solid #E4E2EB; border-radius: 20px; box-shadow: 0 4px 30px rgba(0,0,0,0.02); overflow: hidden;">
        
        <!-- Header -->
        <div style="background-color: #15141C; color: white; padding: 24px; text-align: center; position: relative;">
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 22px; margin: 0 0 6px; font-weight: 700;">
                Let's Build Your Credit Game Plan
            </h1>
            <p style="font-size: 13px; color: #8B879A; margin: 0;">
                Answer a few quick questions to analyze your files.
            </p>
            <!-- Progress Bar -->
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: #1F1E29;">
                <div id="quiz-progress" style="width: 11%; height: 100%; background: #0FA99C; transition: width 0.3s ease;"></div>
            </div>
        </div>

        <form action="{{ route('credit-reports.saveQuestionnaire') }}" method="POST" style="padding: 30px 24px;">
            @csrf

            <!-- STEP 1: GOAL -->
            <div class="quiz-step" id="step-1" style="display: block;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">1. What is your primary credit goal?</h3>
                <div style="display: grid; gap: 12px;">
                    @foreach([
                        'home' => '🏠 Buy a Home (Mortgage Ready)',
                        'auto' => '🚗 Buy a Car (Auto Finance)',
                        'funding' => '💼 Business Funding or Credit Lines',
                        'health' => '🛡️ General Financial Health & Score Boost'
                    ] as $val => $label)
                        <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.15s;">
                            <input type="radio" name="goal" value="{{ $val }}" required {{ $val === 'health' ? 'checked' : '' }}>
                            <span style="font-size: 14px; font-weight: 500; color: #15141C;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 2: IDENTIFIERS -->
            <div class="quiz-step" id="step-2" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">2. Do you have any personal info issues on your file?</h3>
                <p style="font-size: 12.5px; color: #8B879A; margin-top: -12px; margin-bottom: 16px;">Select all that apply on your report.</p>
                <div style="display: grid; gap: 12px;">
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="identifiers[]" value="namevar">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Multiple name variations reporting</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="identifiers[]" value="oldaddr">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Outdated or incorrect addresses</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="identifiers[]" value="otherid">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Wrong employers or other errors</span>
                    </label>
                </div>
            </div>

            <!-- STEP 3: NEGATIVES -->
            <div class="quiz-step" id="step-3" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">3. Which negative accounts are currently reporting?</h3>
                <p style="font-size: 12.5px; color: #8B879A; margin-top: -12px; margin-bottom: 16px;">Select all that apply.</p>
                <div style="display: grid; gap: 12px;">
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="negatives[]" value="collections" id="chk-collections">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Collection Accounts</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="negatives[]" value="chargeoffs" id="chk-chargeoffs">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Charge-off Accounts</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="negatives[]" value="repo">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Repossessions</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="negatives[]" value="lates" id="chk-lates">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Late Payments</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="negatives[]" value="inquiries">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Excessive Hard Inquiries</span>
                    </label>
                </div>
            </div>

            <!-- STEP 4: CHARGE-OFF COUNT -->
            <div class="quiz-step" id="step-4" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">4. How many charge-off accounts do you have?</h3>
                <div style="display: grid; gap: 12px;">
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="co_count" value="one" id="co-count-one">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">Only 1 charge-off</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="co_count" value="two_plus">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">2 or more charge-offs</span>
                    </label>
                </div>
            </div>

            <!-- STEP 5: CHARGE-OFF STATUS -->
            <div class="quiz-step" id="step-5" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">5. What is the status of your charge-off?</h3>
                <div style="display: grid; gap: 12px;">
                    @foreach([
                        'under6' => '🕒 Fresh (Under 6 months old)',
                        'remarked' => '📝 Noted as written off / profit-and-loss loss',
                        'stopped' => '🤫 Stopped reporting (Gone quiet / old)',
                        'activeold' => '🔄 Older, but updating every single month',
                        'unsure' => '🤷 Not sure about the status'
                    ] as $val => $label)
                        <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="co_status" value="{{ $val }}">
                            <span style="font-size: 14px; font-weight: 500; color: #15141C;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 6: LATE PAYMENTS COUNT -->
            <div class="quiz-step" id="step-6" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">6. How many late payments are reporting?</h3>
                <div style="display: grid; gap: 12px;">
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="late_count" value="one_two">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">1 to 2 late payments</span>
                    </label>
                    <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="radio" name="late_count" value="three_plus">
                        <span style="font-size: 14px; font-weight: 500; color: #15141C;">3 or more late payments</span>
                    </label>
                </div>
            </div>

            <!-- STEP 7: CARD UTILIZATION -->
            <div class="quiz-step" id="step-7" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">7. How is your credit card utilization?</h3>
                <div style="display: grid; gap: 12px;">
                    @foreach([
                        'nocards' => '💳 I do not have any credit cards',
                        'under10' => '🟢 Under 10% (Excellent)',
                        '10to30' => '🟡 10% to 30% (Fair)',
                        '30to70' => '🟠 30% to 70% (High Balances)',
                        'maxed' => '🔴 Maxed out (Over 70%)'
                    ] as $val => $label)
                        <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="utilization" value="{{ $val }}" {{ $val === 'under10' ? 'checked' : '' }}>
                            <span style="font-size: 14px; font-weight: 500; color: #15141C;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 8: MIX -->
            <div class="quiz-step" id="step-8" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">8. What is your account mix like?</h3>
                <div style="display: grid; gap: 12px;">
                    @foreach([
                        'none' => '❌ No active credit lines reporting',
                        '1to2' => 'Thin File (Only 1–2 active accounts)',
                        '3plus' => 'Solid Mix (3 or more active accounts)'
                    ] as $val => $label)
                        <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="mix" value="{{ $val }}" {{ $val === '3plus' ? 'checked' : '' }}>
                            <span style="font-size: 14px; font-weight: 500; color: #15141C;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 9: ESTIMATED SCORE -->
            <div class="quiz-step" id="step-9" style="display: none;">
                <h3 style="font-size: 16px; font-weight: 700; color: #15141C; margin-bottom: 20px;">9. What is your estimated credit score range?</h3>
                <div style="display: grid; gap: 12px;">
                    @foreach([
                        'sub580' => '🔴 Under 580 (Foundation)',
                        '580_669' => '🟠 580 to 669 (Strengthening)',
                        '670_739' => '🟡 670 to 739 (Growth)',
                        '740plus' => '🟢 740 or higher (Wealth Building)'
                    ] as $val => $label)
                        <label style="border: 1px solid #E4E2EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="score" value="{{ $val }}" {{ $val === '580_669' ? 'checked' : '' }}>
                            <span style="font-size: 14px; font-weight: 500; color: #15141C;">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Buttons Panel -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 30px; border-top: 1px solid #F0EFF4; padding-top: 20px;">
                <button type="button" id="prev-btn" onclick="goPrev()" style="background: white; border: 1px solid #E4E2EB; border-radius: 8px; padding: 10px 20px; font-size: 13.5px; color: #5C586B; cursor: pointer; visibility: hidden; font-weight: 600;">
                    ← Back
                </button>
                <button type="button" id="next-btn" onclick="goNext()" style="background-color: #0FA99C; color: white; border: none; border-radius: 8px; padding: 10px 24px; font-size: 13.5px; font-weight: 700; cursor: pointer;">
                    Continue →
                </button>
                <button type="submit" id="submit-btn" style="background-color: #0FA99C; color: white; border: none; border-radius: 8px; padding: 10px 24px; font-size: 13.5px; font-weight: 700; cursor: pointer; display: none;">
                    BUILD MY PLAN →
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 9;

    function updateProgress() {
        const percent = Math.round((currentStep / totalSteps) * 100);
        document.getElementById('quiz-progress').style.width = percent + '%';
        
        // Show/hide back button
        if (currentStep === 1) {
            document.getElementById('prev-btn').style.visibility = 'hidden';
        } else {
            document.getElementById('prev-btn').style.visibility = 'visible';
        }

        // Toggle next/submit button
        if (currentStep === totalSteps) {
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('submit-btn').style.display = 'block';
        } else {
            document.getElementById('next-btn').style.display = 'block';
            document.getElementById('submit-btn').style.display = 'none';
        }
    }

    function goNext() {
        // Skip steps logically based on selections
        let nextStep = currentStep + 1;

        if (currentStep === 3) {
            // Check if chargeoffs is checked. If not, skip steps 4 and 5
            const chargeoffs = document.getElementById('chk-chargeoffs').checked;
            const lates = document.getElementById('chk-lates').checked;
            
            if (!chargeoffs) {
                if (lates) {
                    nextStep = 6; // Skip to Late payments
                } else {
                    nextStep = 7; // Skip to Credit card utilization
                }
            }
        } else if (currentStep === 4) {
            // If co_count is "two_plus", we skip status detail step (step 5)
            const isOne = document.getElementById('co-count-one').checked;
            if (!isOne) {
                nextStep = 6; // Skip status details
            }
        } else if (currentStep === 5) {
            // After step 5, skip to late payments (step 6) if late is checked, else step 7
            const lates = document.getElementById('chk-lates').checked;
            if (!lates) {
                nextStep = 7;
            }
        } else if (currentStep === 6) {
            // After late payments count, go to utilization (step 7)
            nextStep = 7;
        }

        if (nextStep <= totalSteps) {
            document.getElementById('step-' + currentStep).style.display = 'none';
            currentStep = nextStep;
            document.getElementById('step-' + currentStep).style.display = 'block';
            updateProgress();
        }
    }

    function goPrev() {
        let prevStep = currentStep - 1;

        // Skip backward logically
        if (currentStep === 7) {
            const lates = document.getElementById('chk-lates').checked;
            const chargeoffs = document.getElementById('chk-chargeoffs').checked;
            if (lates) {
                prevStep = 6;
            } else if (chargeoffs) {
                const isOne = document.getElementById('co-count-one').checked;
                prevStep = isOne ? 5 : 4;
            } else {
                prevStep = 3;
            }
        } else if (currentStep === 6) {
            const chargeoffs = document.getElementById('chk-chargeoffs').checked;
            if (chargeoffs) {
                const isOne = document.getElementById('co-count-one').checked;
                prevStep = isOne ? 5 : 4;
            } else {
                prevStep = 3;
            }
        } else if (currentStep === 5) {
            prevStep = 4;
        }

        if (prevStep >= 1) {
            document.getElementById('step-' + currentStep).style.display = 'none';
            currentStep = prevStep;
            document.getElementById('step-' + currentStep).style.display = 'block';
            updateProgress();
        }
    }
</script>
@endsection
