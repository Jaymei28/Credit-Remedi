<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip check for guests
        if (!$user) {
            return $next($request);
        }

        // Skip check for admins
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Skip check if already on onboarding page
        if ($request->routeIs('identityiq.*')) {
            return $next($request);
        }

        // Skip check for logout route
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Redirect to onboarding if not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('identityiq.onboarding')
                ->with('info', 'Please complete the IdentityIQ setup to access your dashboard.');
        }

        return $next($request);
    }
}
