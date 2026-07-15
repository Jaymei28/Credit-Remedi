<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProAccess
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

        // Check if user has pro or premium plan
        if (!in_array(auth()->user()->plan_type, ['pro', 'premium'])) {
            return redirect()->route('dashboard')
                ->with('error', '⭐ This feature is only available for Pro and Premium subscribers. Upgrade your plan to access Fundability Score & Lender Matching!');
        }

        return $next($request);
    }
}
