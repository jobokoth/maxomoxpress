<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentPortalAccess
{
    /**
     * Verify the authenticated user has a Student record for the current school.
     * Binds the student to the request as 'portal_student'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $school = app('current_school');

        if (! $user || ! $school) {
            abort(403);
        }

        $student = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('school_id', $school->id)
            ->with(['batch', 'course', 'academicYear'])
            ->first();

        if (! $student) {
            abort(403, 'You do not have student portal access for this school.');
        }

        $request->attributes->set('portal_student', $student);

        return $next($request);
    }
}
