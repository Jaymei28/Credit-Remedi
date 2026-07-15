<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    /**
     * Handle invoice payment succeeded.
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        $invoice = $payload['data']['object'];
        $user = $this->getUserByStripeId($invoice['customer']);

        if ($user) {
            $user->has_paid = true;
            $user->registration_status = 'completed';
            $user->registration_error = null;
            $user->save();

            Log::info("Payment succeeded for user: {$user->email}");
            
            // Optional: Send success email
            // Mail::to($user)->send(new PaymentSuccessful($invoice));
        }

        return $this->successMethod();
    }

    /**
     * Handle invoice payment failed.
     */
    public function handleInvoicePaymentFailed($payload)
    {
        $invoice = $payload['data']['object'];
        $user = $this->getUserByStripeId($invoice['customer']);

        if ($user) {
            $user->has_paid = false;
            // Only set to failed if it wasn't already completed
            if ($user->registration_status !== 'completed') {
                $user->registration_status = 'failed';
                $user->registration_error = 'Invoice payment failed';
            }
            $user->save();

            Log::warning("Payment failed for user: {$user->email}");
            
            // Send payment failed notification
            // Mail::to($user)->send(new PaymentFailed($invoice));
        }

        return $this->successMethod();
    }

    /**
     * Handle customer subscription deleted.
     */
    public function handleCustomerSubscriptionDeleted($payload)
    {
        $subscription = $payload['data']['object'];
        $user = $this->getUserByStripeId($subscription['customer']);

        if ($user) {
            $user->has_paid = false;
            $user->save();

            Log::info("Subscription deleted for user: {$user->email}");
            
            // Send subscription cancelled email
            // Mail::to($user)->send(new SubscriptionCancelled());
        }

        return $this->successMethod();
    }

    /**
     * Handle customer subscription updated.
     */
    public function handleCustomerSubscriptionUpdated($payload)
    {
        $subscription = $payload['data']['object'];
        $user = $this->getUserByStripeId($subscription['customer']);

        if ($user) {
            // Check if subscription is active
            $isActive = in_array($subscription['status'], ['active', 'trialing']);
            $user->has_paid = $isActive;
            
            if ($isActive) {
                $user->registration_status = 'completed';
                $user->registration_error = null;
            }

            // Update plan type if changed
            $stripePriceId = $subscription['items']['data'][0]['price']['id'];
            if ($stripePriceId === env('STRIPE_PRICE_PREMIUM')) {
                $user->plan_type = 'premium';
            } elseif ($stripePriceId === env('STRIPE_PRICE_STARTER')) {
                $user->plan_type = 'starter';
            }
            
            $user->save();

            Log::info("Subscription updated for user: {$user->email}, Status: {$subscription['status']}");
        }

        return $this->successMethod();
    }

    /**
     * Get user by Stripe customer ID.
     */
    protected function getUserByStripeId($stripeId)
    {
        return User::where('stripe_id', $stripeId)->first();
    }
}