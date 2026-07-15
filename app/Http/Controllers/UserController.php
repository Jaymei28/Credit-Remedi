<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;



class UserController extends Controller
{
    // Show all users
    public function index()
    {
        $users = User::latest()->paginate(10); // paginate 10 per page
        return view('admin.users.index', compact('users'));
    }

    // Show create form
    public function create()
    {
        return view('users.create');
    }

    // Store new user
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,regular',
            'plan_type' => 'nullable|in:starter,standard,pro,premium',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'plan_type' => $request->plan_type,
            'ssn_last4' => '0000', // Default value for admin-created users
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // Show edit form
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,regular',
            'plan_type' => 'nullable|in:starter,standard,pro,premium',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
            'plan_type' => $request->plan_type,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // Delete user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function showRegisterForm()
    {
        return view('register');
    }

    


    public function register(Request $request)
    {
        if ($request->filled('website')) {
            abort(403, 'Bot detected.');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6|confirmed',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:50',
            'zipcode'        => 'required|string|max:10',
            'contact_number' => 'required|string|max:255',
            'ssn_last4'      => 'required|digits:4',
            'paypalOrderId'  => 'required|string',
            'selected_plan'  => 'required|string|in:starter,premium',
        ]);

        DB::beginTransaction();

        try {
            // Create user with pending status - commit to DB immediately
            $user = User::create([
                'name'                => $validated['name'],
                'email'               => $validated['email'],
                'password'            => Hash::make($validated['password']),
                'address'             => $validated['address'],
                'city'                => $validated['city'],
                'state'               => $validated['state'],
                'zipcode'             => $validated['zipcode'],
                'contact_number'      => $validated['contact_number'],
                'ssn_last4'           => $validated['ssn_last4'],
                'has_paid'            => false,
                'role'                => 'regular',
                'plan_type'           => $validated['selected_plan'],
                'registration_status' => 'pending',
                'payment_attempted_at' => now(),
            ]);

            // Commit user to database BEFORE attempting Stripe operations
            DB::commit();

            // Now attempt PayPal subscription verification
            try {
                $paypalCaptured = $this->verifyPayPalSubscription($validated['paypalOrderId']);

                if (!$paypalCaptured) {
                    throw new \Exception('Failed to verify PayPal subscription.');
                }

                // Payment successful - update user status
                $user->update([
                    'has_paid' => true,
                    'registration_status' => 'completed',
                    'registration_error' => null,
                ]);

                Auth::login($user);
                return redirect('/thank-you?next=onboarding')
                    ->with('success', 'Account created successfully! Please complete the IdentityIQ setup to get started.');

            } catch (\Exception $paypalError) {
                // PayPal operation failed - update user with error details
                $user->update([
                    'registration_status' => 'failed',
                    'registration_error' => $paypalError->getMessage(),
                ]);

                // Log the error for admin review
                Log::error('User registration payment failed (PayPal)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'plan' => $validated['selected_plan'],
                    'error' => $paypalError->getMessage(),
                    'timestamp' => now(),
                ]);

                return redirect()->back()
                    ->with('error', 'Registration failed: ' . $paypalError->getMessage() . ' Please contact support or try again.')
                    ->withInput();
            }

        } catch (\Exception $e) {
            // User creation failed - rollback
            DB::rollBack();
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage());
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
