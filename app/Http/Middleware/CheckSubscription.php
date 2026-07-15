<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Check if user has an active subscription
        if (!$user->subscribed('default')) {
            return redirect()
                ->route('billing')
                ->with('error', 'Your subscription is inactive. Please update your payment method.');
        }

        return $next($request);
    }
}
