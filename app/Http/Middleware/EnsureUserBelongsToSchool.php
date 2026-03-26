<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $request->attributes->get('current_school');
        $user = $request->user();

        if (! $school || ! $user) {
            abort(403, 'Unauthorized school access.');
        }

        $belongsToSchool = $user->schools()
            ->where('schools.id', $school->id)
            ->where(function ($query): void {
                $query->whereNull('school_user.left_at')
                    ->orWhere('school_user.left_at', '>', now());
            })
            ->exists();

        abort_unless($belongsToSchool, 403, 'Unauthorized school access.');

        return $next($request);
    }
}
