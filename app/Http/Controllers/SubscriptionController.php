<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    public function show()
    {
        if (auth()->user()->has_paid || auth()->user()->role === 'admin') {
            return redirect('/dashboard')->with('info', 'You already have access.');
        }
        return view('subscribe');
    }

    public function store(Request $request)
    {
        $request->validate([
            'paypalOrderId' => 'required|string',
            'selected_plan' => 'required|string|in:starter,premium',
        ]);

        $user = Auth::user();

        try {
            // Using the same subscription verification logic as the registration page
            $paypalCaptured = $this->verifyPayPalSubscription($request->paypalOrderId);

            if (!$paypalCaptured) {
                throw new \Exception('Failed to verify PayPal subscription.');
            }

            $user->update([
                'has_paid' => true,
                'plan_type' => $request->selected_plan,
            ]);

            return redirect('/thank-you?next=dashboard')->with('success', 'Payment successful! Your monthly plan is now active.');

        } catch (\Exception $e) {
            Log::error('Subscription payment failed (PayPal)', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the user's subscription in Stripe and update DB.
     */
    public function unsubscribe(Request $request)
    {
        $user = Auth::user();

        \Log::info('Unsubscribe request for user: ' . $user->id);

        if (!$user->stripe_id) {
            \Log::error('No Stripe ID found for user ' . $user->id);
            return response()->json(['message' => 'No active subscription found.'], 400);
        }

        try {
            \Log::info('Stripe ID: ' . $user->stripe_id);

            \Stripe\Stripe::setApiKey(config('cashier.secret'));

            if ($user->subscribed('default')) {
                \Log::info('User has active subscription, cancelling...');
                $user->subscription('default')->cancel();
            } else {
                \Log::error('User has no subscription record in DB.');
                return response()->json(['message' => 'No active subscription to cancel.'], 400);
            }

            $user->has_paid = false;
            $user->plan_type = null;
            $user->save();

            \Log::info('Subscription cancelled successfully for user ' . $user->id);

            return response()->json(['message' => 'Your subscription has been cancelled.']);

        } catch (\Exception $e) {
            \Log::error('Unsubscribe error: ' . $e->getMessage());
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    private function verifyPayPalSubscription($subscriptionId)
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'live');
        $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // Get Access Token
        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        if (!$response->successful()) {
            Log::error('PayPal Auth Failed', ['response' => $response->json()]);
            return false;
        }

        $accessToken = $response->json()['access_token'];

        // Get Subscription Details
        $subscriptionResponse = Http::withToken($accessToken)
            ->get("{$baseUrl}/v1/billing/subscriptions/{$subscriptionId}");

        if (!$subscriptionResponse->successful()) {
            Log::error('PayPal Subscription Verification Failed', ['response' => $subscriptionResponse->json()]);
            return false;
        }

        $result = $subscriptionResponse->json();
        // Check if status is ACTIVE or APPROVAL_PENDING
        return isset($result['status']) && in_array($result['status'], ['ACTIVE', 'APPROVED']);
    }

}
