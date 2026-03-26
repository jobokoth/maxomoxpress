<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotOnboarded
{
    /**
     * Handle an incoming request.
     *
     * If the authenticated user belongs to any school that has not completed
     * onboarding, redirect them to the wizard — unless they are already on
     * the onboarding route or are a super_admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Super admins bypass onboarding — send to the platform admin panel
        if ($user->hasRole('super_admin')) {
            return redirect('/platform-admin');
        }

        // If all schools are already onboarded, redirect to the school app
        $hasUnonboarded = $user->schools()->whereNull('onboarding_completed_at')->exists();

        if (! $hasUnonboarded) {
            // Send them to the first onboarded school's dashboard
            $school = $user->schools()->whereNotNull('onboarding_completed_at')->first();

            if ($school) {
                return redirect()->route('tenant.dashboard', $school->slug);
            }

            // Fallback: no onboarded school found, let them re-register
            return redirect()->route('school.register');
        }

        return $next($request);
    }
}
