<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPremiumAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow admins to access everything
        if (auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Check if user has a paid plan (starter, standard, pro, or premium)
        $allowedPlans = ['starter', 'standard', 'pro', 'premium'];
        if (!in_array(auth()->user()->plan_type, $allowedPlans)) {
            return redirect()->route('dashboard')
                ->with('error', '⭐ This feature is only available for paid subscribers. Upgrade your plan to access Credit Remedi AI!');
        }

        return $next($request);
    }
}
