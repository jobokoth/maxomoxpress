<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $schoolSlug = $request->route('school_slug');

        if (! $schoolSlug) {
            return $next($request);
        }

        $school = School::query()
            ->where('slug', $schoolSlug)
            ->where('is_active', true)
            ->first();

        abort_if(! $school, 404, 'School not found.');

        app()->instance('current_school', $school);
        $request->attributes->set('current_school', $school);
        session(['school_id' => $school->id]);

        return $next($request);
    }
}

