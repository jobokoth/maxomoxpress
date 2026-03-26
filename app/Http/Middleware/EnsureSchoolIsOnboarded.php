<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolIsOnboarded
{
    /**
     * Handle an incoming request.
     *
     * Redirect authenticated users to the onboarding wizard if they belong
     * to a school that has not completed onboarding. Skips super_admin users.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Super admins never need to onboard
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Check if the user has any school that is not yet onboarded
        $hasUnonboardedSchool = $user->schools()
            ->whereNull('onboarding_completed_at')
            ->exists();

        if ($hasUnonboardedSchool) {
            return redirect()->route('onboarding')
                ->with('info', 'Please complete your school setup before continuing.');
        }

        return $next($request);
    }
}
