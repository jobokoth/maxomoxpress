<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Batch;
use App\Models\Course;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DailyOperationsController extends Controller
{
    public function attendance(Request $request): View
    {
        $school = app('current_school');
        $schoolId = $school->id;

        $date = $request->input('date', now()->toDateString());
        $batchId = $request->input('batch_id');

        $students = Student::query()
            ->with(['course', 'batch'])
            ->when($batchId, fn ($query) => $query->where('batch_id', $batchId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $studentAttendanceMap = StudentAttendance::query()
            ->where('attendance_date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $staffUsers = User::query()
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
            ->orderBy('name')
            ->get();

        $staffAttendanceMap = StaffAttendance::query()
            ->where('attendance_date', $date)
            ->whereIn('user_id', $staffUsers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('operations.attendance.index', [
            'school' => $school,
            'date' => $date,
            'selectedBatchId' => $batchId,
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'students' => $students,
            'studentAttendanceMap' => $studentAttendanceMap,
            'staffUsers' => $staffUsers,
            'staffAttendanceMap' => $staffAttendanceMap,
        ]);
    }

    public function storeStudentAttendance(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'student_statuses' => ['required', 'array'],
            'student_statuses.*' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $studentIds = array_keys($validated['student_statuses']);

        $validStudentIds = Student::query()
            ->whereIn('id', $studentIds)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        DB::transaction(function () use ($validated, $studentIds, $validStudentIds, $schoolId, $request): void {
            foreach ($studentIds as $studentId) {
                if (! in_array((string) $studentId, $validStudentIds, true)) {
                    continue;
                }

                $student = Student::query()->find($studentId);
                if (! $student) {
                    continue;
                }

                StudentAttendance::query()->updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'attendance_date' => $validated['attendance_date'],
                        'student_id' => $studentId,
                    ],
                    [
                        'course_id' => $student->course_id,
                        'batch_id' => $student->batch_id,
                        'status' => $validated['student_statuses'][$studentId],
                        'remarks' => $validated['remarks'][$studentId] ?? null,
                        'marked_by_user_id' => $request->user()?->id,
                    ]
                );
            }
        });

        return back()->with('status', 'Student attendance saved.');
    }

    public function storeStaffAttendance(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'staff_statuses' => ['required', 'array'],
            'staff_statuses.*' => ['required', Rule::in(['present', 'absent', 'late', 'on_leave'])],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $userIds = array_keys($validated['staff_statuses']);

        $validUserIds = User::query()
            ->whereIn('id', $userIds)
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        DB::transaction(function () use ($validated, $userIds, $validUserIds, $schoolId, $request): void {
            foreach ($userIds as $userId) {
                if (! in_array((string) $userId, $validUserIds, true)) {
                    continue;
                }

                StaffAttendance::query()->updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'attendance_date' => $validated['attendance_date'],
                        'user_id' => $userId,
                    ],
                    [
                        'status' => $validated['staff_statuses'][$userId],
                        'remarks' => $validated['remarks'][$userId] ?? null,
                        'marked_by_user_id' => $request->user()?->id,
                    ]
                );
            }
        });

        return back()->with('status', 'Staff attendance saved.');
    }

    public function timetable(): View
    {
        $school = app('current_school');

        $entries = TimetableEntry::query()
            ->with(['course', 'batch', 'subject', 'teacher', 'term', 'academicYear'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);

        return view('operations.timetable.index', [
            'school' => $school,
            'entries' => $entries,
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderByDesc('start_date')->get(),
            'teachers' => User::query()
                ->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeTimetableEntry(Request $request): RedirectResponse
    {
        $validated = $this->validateTimetableEntry($request);

        $this->ensureNoTimeConflict($validated);

        TimetableEntry::query()->create($validated);

        return back()->with('status', 'Timetable entry created.');
    }

    public function updateTimetableEntry(Request $request, TimetableEntry $entry): RedirectResponse
    {
        $validated = $this->validateTimetableEntry($request);

        $this->ensureNoTimeConflict($validated, $entry->id);

        $entry->update($validated);

        return back()->with('status', 'Timetable entry updated.');
    }

    public function destroyTimetableEntry(TimetableEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('status', 'Timetable entry removed.');
    }

    public function announcements(): View
    {
        $school = app('current_school');

        $announcements = Announcement::query()
            ->with(['course', 'batch', 'createdBy'])
            ->withCount('reads')
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(20);

        return view('operations.announcements.index', [
            'school' => $school,
            'announcements' => $announcements,
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $this->validateAnnouncement($request);
        $validated['created_by_user_id'] = $request->user()?->id;

        Announcement::query()->create($validated);

        return back()->with('status', 'Announcement published.');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $this->validateAnnouncement($request);

        $announcement->update($validated);

        return back()->with('status', 'Announcement updated.');
    }

    public function destroyAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'Announcement removed.');
    }

    public function markAnnouncementRead(Announcement $announcement, Request $request): RedirectResponse
    {
        $announcement->reads()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['read_at' => now()]
        );

        return back();
    }

    private function validateTimetableEntry(Request $request): array
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['nullable', Rule::exists('users', 'id')],
            'day_of_week' => ['required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['teacher_user_id'])) {
            $belongs = User::query()
                ->where('id', $validated['teacher_user_id'])
                ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
                ->exists();

            abort_unless($belongs, 422, 'Selected teacher does not belong to this school.');
        }

        return $validated;
    }

    private function ensureNoTimeConflict(array $validated, ?int $ignoreId = null): void
    {
        $query = TimetableEntry::query()
            ->where('course_id', $validated['course_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($inner) use ($validated): void {
                $inner->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            });

        if (! empty($validated['batch_id'])) {
            $query->where(function ($inner) use ($validated): void {
                $inner->whereNull('batch_id')
                    ->orWhere('batch_id', $validated['batch_id']);
            });
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        abort_if($query->exists(), 422, 'Time conflict detected for this class/batch.');
    }

    private function validateAnnouncement(Request $request): array
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', Rule::in(['all', 'staff', 'students', 'parents', 'class'])],
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        if (($validated['audience'] ?? null) === 'class' && empty($validated['course_id'])) {
            abort(422, 'Course is required for class announcements.');
        }

        if (blank($validated['published_at'] ?? null)) {
            $validated['published_at'] = Carbon::now();
        }

        return $validated;
    }
}
