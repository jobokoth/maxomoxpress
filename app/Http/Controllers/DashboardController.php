<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Department;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function landing()
    {
        $school = School::query()->where('is_active', true)->first();

        if (! $school) {
            return response()->view('setup-required', [], 500);
        }

        return redirect()->route('tenant.dashboard', ['school_slug' => $school->slug]);
    }

    public function index(): View
    {
        $school = app('current_school');

        $stats = [
            'users' => User::query()->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))->count(),
            'students' => Student::query()->count(),
            'departments' => Department::query()->count(),
            'courses' => Course::query()->count(),
            'subjects' => Subject::query()->count(),
            'batches' => Batch::query()->count(),
            'terms' => Term::query()->count(),
        ];

        $currentYear = AcademicYear::query()->where('is_current', true)->first();
        $recentDepartments = Department::query()->latest()->take(5)->get();
        $recentCourses = Course::query()->with('department')->latest()->take(6)->get();

        return view('dashboard.index', compact('school', 'stats', 'currentYear', 'recentDepartments', 'recentCourses'));
    }
}
