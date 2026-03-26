<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\BookIssue;
use App\Models\FeeAssignment;
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentMark;
use App\Models\Term;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    private function student(Request $request): Student
    {
        return $request->attributes->get('portal_student');
    }

    public function dashboard(Request $request): View
    {
        $school = app('current_school');
        $student = $this->student($request);

        $feeSummary = FeeAssignment::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->selectRaw('SUM(final_amount) as total_billed, SUM(paid_amount) as total_paid, SUM(balance_amount) as total_balance')
            ->first();

        $recentAttendance = StudentAttendance::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('attendance_date')
            ->take(30)
            ->get();

        $attendanceSummary = [
            'present' => $recentAttendance->where('status', 'present')->count(),
            'absent' => $recentAttendance->where('status', 'absent')->count(),
            'total' => $recentAttendance->count(),
        ];
        $attendanceSummary['rate'] = $attendanceSummary['total'] > 0
            ? round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100, 1)
            : 0;

        $announcements = Announcement::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->latest('published_at')
            ->take(4)
            ->get();

        $events = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at')
            ->take(4)
            ->get();

        $activeBook = BookIssue::withoutGlobalScopes()
            ->with('book')
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->whereNull('returned_at')
            ->get();

        return view('portal.student.dashboard', compact(
            'school', 'student', 'feeSummary',
            'attendanceSummary', 'announcements', 'events', 'activeBook'
        ));
    }

    public function timetable(Request $request): View
    {
        $school = app('current_school');
        $student = $this->student($request);

        $currentTerm = Term::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $timetable = collect();
        if ($student->batch_id && $currentTerm) {
            $timetable = TimetableEntry::withoutGlobalScopes()
                ->with(['subject', 'teacher'])
                ->where('school_id', $school->id)
                ->where('batch_id', $student->batch_id)
                ->where('term_id', $currentTerm->id)
                ->where('is_active', true)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('portal.student.timetable', compact(
            'school', 'student', 'timetable', 'days', 'currentTerm'
        ));
    }

    public function results(Request $request): View
    {
        $school = app('current_school');
        $student = $this->student($request);

        $marks = StudentMark::withoutGlobalScopes()
            ->with(['exam', 'subject', 'schedule'])
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('exam_id');

        return view('portal.student.results', compact('school', 'student', 'marks'));
    }

    public function attendance(Request $request): View
    {
        $school = app('current_school');
        $student = $this->student($request);

        $attendance = StudentAttendance::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('attendance_date')
            ->paginate(30);

        $allAttendance = StudentAttendance::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->get();

        $summary = [
            'present' => $allAttendance->where('status', 'present')->count(),
            'absent' => $allAttendance->where('status', 'absent')->count(),
            'late' => $allAttendance->where('status', 'late')->count(),
            'excused' => $allAttendance->where('status', 'excused')->count(),
            'total' => $allAttendance->count(),
        ];
        $summary['rate'] = $summary['total'] > 0
            ? round(($summary['present'] / $summary['total']) * 100, 1)
            : 0;

        return view('portal.student.attendance', compact(
            'school', 'student', 'attendance', 'summary'
        ));
    }

    public function library(Request $request): View
    {
        $school = app('current_school');
        $student = $this->student($request);

        $currentIssues = BookIssue::withoutGlobalScopes()
            ->with('book')
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->whereNull('returned_at')
            ->orderByDesc('issued_at')
            ->get();

        $history = BookIssue::withoutGlobalScopes()
            ->with('book')
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->whereNotNull('returned_at')
            ->orderByDesc('returned_at')
            ->take(20)
            ->get();

        return view('portal.student.library', compact(
            'school', 'student', 'currentIssues', 'history'
        ));
    }
}
