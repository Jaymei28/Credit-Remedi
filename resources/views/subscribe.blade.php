@extends('layouts.app')

@section('title', 'Subscribe')

@section('content')
<style>
     @media (min-width: 1200px) {
        .container {
            max-width: 1140px; /* slightly less than default xl */
        }
    }
</style>
<div class="container mt-5">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

<div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center fw-semibold">
                    🚀 Upgrade Your Account - Monthly Access
                </div>

                <div class="card-body p-4">
                    <div class="row mb-4 text-center">
                        <div class="col-md-6 border-end">
                            <div class="p-3">
                                <h4 class="fw-bold">Standard Plan</h4>
                                <div class="display-6 mb-2">$49.99<span class="fs-6 text-muted">/mo</span></div>
                                <button class="btn btn-outline-primary w-100 select-plan" data-plan="starter">Select Starter</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <h4 class="fw-bold text-success">Turbo Plan</h4>
                                <div class="display-6 mb-2">$69.99<span class="fs-6 text-muted">/mo</span></div>
                                <button class="btn btn-outline-success w-100 select-plan" data-plan="premium">Select Premium</button>
                            </div>
                        </div>
                    </div>

                    <div id="payment-section" style="display: none;">
                        <hr>
                        <form action="{{ route('subscribe') }}" method="POST" id="payment-form">
                            @csrf
                            <input type="hidden" name="selected_plan" id="selected_plan">
                            <input type="hidden" name="paypalOrderId" id="paypalOrderId">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-center d-block">
                                    💳 Pay with PayPal for <span id="display-plan-name" class="text-primary"></span>
                                </label>
                                <div id="paypal-button-container"></div>
                                <div id="paypal-errors" class="text-danger mt-2 small text-center"></div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-4 text-center small text-muted">
                        Secured by PayPal. Cancel anytime. Subscription starts immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('payment-form');
        const paypalOrderIdInput = document.getElementById('paypalOrderId');
        const selectedPlanInput = document.getElementById('selected_plan');
        const planNameDisplay = document.getElementById('display-plan-name');
        const paymentSection = document.getElementById('payment-section');
        const errorDiv = document.getElementById('paypal-errors');

        // Plan IDs from Config
        const planIds = {
            'starter': "{{ config('services.paypal.starter_plan_id') }}",
            'premium': "{{ config('services.paypal.premium_plan_id') }}"
        };

        let currentPaypalButtons = null;

        document.querySelectorAll('.select-plan').forEach(button => {
            button.addEventListener('click', function() {
                const plan = this.getAttribute('data-plan');
                
                // Update UI
                document.querySelectorAll('.select-plan').forEach(btn => btn.classList.replace('btn-primary', 'btn-outline-primary'));
                document.querySelectorAll('.select-plan').forEach(btn => btn.classList.replace('btn-success', 'btn-outline-success'));
                
                if (plan === 'starter') {
                    this.classList.replace('btn-outline-primary', 'btn-primary');
                } else {
                    this.classList.replace('btn-outline-success', 'btn-success');
                }

                selectedPlanInput.value = plan;
                planNameDisplay.textContent = plan.charAt(0).toUpperCase() + plan.slice(1) + " Plan";
                paymentSection.style.display = 'block';

                // Re-render PayPal buttons for selected plan
                if (currentPaypalButtons) {
                    currentPaypalButtons.close();
                }

                renderPaypalButtons(planIds[plan]);
            });
        });

        function renderPaypalButtons(planId) {
            currentPaypalButtons = paypal.Buttons({
                style: {
                    shape: 'rect',
                    color: 'gold',
                    layout: 'vertical',
                    label: 'subscribe'
                },
                createSubscription: function(data, actions) {
                    return actions.subscription.create({
                        'plan_id': planId
                    });
                },
                onApprove: function(data, actions) {
                    paypalOrderIdInput.value = data.subscriptionID;
                    
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your subscription is being activated...',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        form.submit();
                    });
                },
                onError: function(err) {
                    errorDiv.textContent = "Error creating subscription. Please try again.";
                    console.error(err);
                }
            });
            currentPaypalButtons.render('#paypal-button-container');
        }

        form.addEventListener('submit', (e) => {
            if (!paypalOrderIdInput.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Payment Required',
                    text: 'Please complete the PayPal payment.',
                    icon: 'warning'
                });
            }
        });
    });
</script>
@endpush
