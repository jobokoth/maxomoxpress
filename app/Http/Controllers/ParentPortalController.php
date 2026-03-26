<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FeeAssignment;
use App\Models\Guardian;
use App\Models\SchoolEvent;
use App\Models\StudentMark;
use Illuminate\Contracts\View\View;

class ParentPortalController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $school = app('current_school');

        $guardian = Guardian::query()->with(['students.course', 'students.batch'])
            ->where('user_id', $user->id)
            ->first();

        $children = $guardian?->students ?? collect();
        $studentIds = $children->pluck('id');

        return view('communication.parent-portal', [
            'school' => $school,
            'guardian' => $guardian,
            'children' => $children,
            'announcements' => Announcement::query()->latest('published_at')->take(10)->get(),
            'events' => SchoolEvent::query()->where('start_at', '>=', now()->startOfDay())->orderBy('start_at')->take(10)->get(),
            'fees' => FeeAssignment::query()->with(['student', 'structure'])->whereIn('student_id', $studentIds)->latest()->take(20)->get(),
            'marks' => StudentMark::query()->with(['student', 'exam', 'subject'])->whereIn('student_id', $studentIds)->latest()->take(20)->get(),
        ]);
    }
}
