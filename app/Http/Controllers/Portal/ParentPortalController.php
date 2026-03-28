<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FeeAssignment;
use App\Models\FeePayment;
use App\Models\Guardian;
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentMark;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    private function guardian(Request $request): Guardian
    {
        return $request->attributes->get('portal_guardian');
    }

    public function dashboard(Request $request): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);
        $children = $guardian->students;
        $studentIds = $children->pluck('id');

        $feeSummary = FeeAssignment::withoutGlobalScopes()
            ->whereIn('student_id', $studentIds)
            ->where('school_id', $school->id)
            ->selectRaw('SUM(final_amount) as total_billed, SUM(paid_amount) as total_paid, SUM(balance_amount) as total_balance')
            ->first();

        $recentPayments = FeePayment::withoutGlobalScopes()
            ->with('student')
            ->whereIn('student_id', $studentIds)
            ->where('school_id', $school->id)
            ->latest('payment_date')
            ->take(5)
            ->get();

        $announcements = Announcement::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        $events = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at')
            ->take(5)
            ->get();

        return view('portal.parent.dashboard', compact(
            'school', 'guardian', 'children',
            'feeSummary', 'recentPayments', 'announcements', 'events'
        ));
    }

    public function fees(Request $request, Student $student): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        abort_unless(
            $guardian->students->contains($student->id),
            403,
            'This student is not linked to your account.'
        );

        $feeAssignments = FeeAssignment::withoutGlobalScopes()
            ->with(['structure', 'term', 'academicYear', 'payments'])
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('created_at')
            ->get();

        $totalBilled = $feeAssignments->sum('final_amount');
        $totalPaid = $feeAssignments->sum('paid_amount');
        $totalBalance = $feeAssignments->sum('balance_amount');

        return view('portal.parent.fees', compact(
            'school', 'guardian', 'student',
            'feeAssignments', 'totalBilled', 'totalPaid', 'totalBalance'
        ));
    }

    public function attendance(Request $request, Student $student): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        abort_unless(
            $guardian->students->contains($student->id),
            403,
            'This student is not linked to your account.'
        );

        $attendance = StudentAttendance::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('attendance_date')
            ->take(90)
            ->get();

        $summary = [
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
            'total' => $attendance->count(),
        ];
        $summary['rate'] = $summary['total'] > 0
            ? round(($summary['present'] / $summary['total']) * 100, 1)
            : 0;

        return view('portal.parent.attendance', compact(
            'school', 'guardian', 'student', 'attendance', 'summary'
        ));
    }

    public function results(Request $request, Student $student): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        abort_unless(
            $guardian->students->contains($student->id),
            403,
            'This student is not linked to your account.'
        );

        $marks = StudentMark::withoutGlobalScopes()
            ->with(['exam', 'subject', 'schedule'])
            ->where('student_id', $student->id)
            ->where('school_id', $school->id)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('exam_id');

        return view('portal.parent.results', compact(
            'school', 'guardian', 'student', 'marks'
        ));
    }

    public function announcements(Request $request): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        $announcements = Announcement::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->latest('published_at')
            ->paginate(20);

        return view('portal.parent.announcements', compact('school', 'guardian', 'announcements'));
    }

    public function events(Request $request): View
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        $events = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->orderBy('start_at')
            ->paginate(20);

        return view('portal.parent.events', compact('school', 'guardian', 'events'));
    }

    public function grantStudentAccess(Request $request, Student $student): \Illuminate\Http\RedirectResponse
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        abort_unless(
            $guardian->students->contains($student->id),
            403,
            'This student is not linked to your account.'
        );

        Student::withoutGlobalScopes()
            ->where('id', $student->id)
            ->where('school_id', $school->id)
            ->update(['portal_access_granted' => true]);

        return back()->with('success', $student->first_name . ' has been granted access to the student portal.');
    }

    public function revokeStudentAccess(Request $request, Student $student): \Illuminate\Http\RedirectResponse
    {
        $school = app('current_school');
        $guardian = $this->guardian($request);

        abort_unless(
            $guardian->students->contains($student->id),
            403,
            'This student is not linked to your account.'
        );

        Student::withoutGlobalScopes()
            ->where('id', $student->id)
            ->where('school_id', $school->id)
            ->update(['portal_access_granted' => false]);

        return back()->with('success', $student->first_name . '\'s student portal access has been revoked.');
    }
}
