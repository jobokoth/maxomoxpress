<?php

namespace App\Http\Middleware;

use App\Models\Guardian;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentPortalAccess
{
    /**
     * Verify the authenticated user has a Guardian record for the current school.
     * Binds the guardian to the request as 'portal_guardian'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $school = app('current_school');

        if (! $user || ! $school) {
            abort(403);
        }

        $guardian = Guardian::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('school_id', $school->id)
            ->with(['students.batch', 'students.course', 'students.academicYear'])
            ->first();

        if (! $guardian) {
            abort(403, 'You do not have parent portal access for this school.');
        }

        $request->attributes->set('portal_guardian', $guardian);

        return $next($request);
    }
}
