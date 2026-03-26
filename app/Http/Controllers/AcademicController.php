<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Course;
use App\Models\CourseSubjectAssignment;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicController extends Controller
{
    public function academicYears(): View
    {
        return view('academic.years.index', [
            'school' => app('current_school'),
            'years' => AcademicYear::query()->latest('start_date')->paginate(12),
        ]);
    }

    public function storeAcademicYear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $isCurrent = (bool) ($validated['is_current'] ?? false);

        if ($isCurrent) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        AcademicYear::query()->create($validated);

        return back()->with('status', 'Academic year created.');
    }

    public function editAcademicYear(AcademicYear $academicYear): View
    {
        return view('academic.years.edit', [
            'school' => app('current_school'),
            'academicYear' => $academicYear,
        ]);
    }

    public function updateAcademicYear(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $isCurrent = (bool) ($validated['is_current'] ?? false);

        if ($isCurrent) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        $academicYear->update($validated);

        return redirect()->route('tenant.academic-years.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Academic year updated.');
    }

    public function terms(): View
    {
        return view('academic.terms.index', [
            'school' => app('current_school'),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->with('academicYear')->latest('start_date')->paginate(15),
        ]);
    }

    public function storeTerm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        if ((bool) ($validated['is_current'] ?? false)) {
            Term::query()->update(['is_current' => false]);
        }

        Term::query()->create($validated);

        return back()->with('status', 'Term created.');
    }

    public function editTerm(Term $term): View
    {
        return view('academic.terms.edit', [
            'school' => app('current_school'),
            'term' => $term,
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function updateTerm(Request $request, Term $term): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        if ((bool) ($validated['is_current'] ?? false)) {
            Term::query()->update(['is_current' => false]);
        }

        $term->update($validated);

        return redirect()->route('tenant.terms.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Term updated.');
    }

    public function departments(): View
    {
        return view('academic.departments.index', [
            'school' => app('current_school'),
            'teachers' => $this->teacherUsers(),
            'departments' => Department::query()->with('head')->latest()->paginate(15),
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'head_user_id' => ['nullable', Rule::exists('users', 'id')],
            'description' => ['nullable', 'string'],
        ]);

        $this->abortIfUserOutsideSchool($validated['head_user_id'] ?? null);

        Department::query()->create($validated);

        return back()->with('status', 'Department created.');
    }

    public function editDepartment(Department $department): View
    {
        return view('academic.departments.edit', [
            'school' => app('current_school'),
            'department' => $department,
            'teachers' => $this->teacherUsers(),
        ]);
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:30'],
            'head_user_id' => ['nullable', Rule::exists('users', 'id')],
            'description' => ['nullable', 'string'],
        ]);

        $this->abortIfUserOutsideSchool($validated['head_user_id'] ?? null);

        $department->update($validated);

        return redirect()->route('tenant.departments.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Department updated.');
    }

    public function courses(): View
    {
        return view('academic.courses.index', [
            'school' => app('current_school'),
            'departments' => Department::query()->orderBy('name')->get(),
            'courses' => Course::query()->with('department')->latest()->paginate(15),
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'course_type' => ['required', Rule::in(['primary', 'secondary', 'tertiary', 'vocational'])],
            'duration_years' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        Course::query()->create($validated);

        return back()->with('status', 'Course created.');
    }

    public function editCourse(Course $course): View
    {
        return view('academic.courses.edit', [
            'school' => app('current_school'),
            'course' => $course,
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'course_type' => ['required', Rule::in(['primary', 'secondary', 'tertiary', 'vocational'])],
            'duration_years' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $course->update($validated);

        return redirect()->route('tenant.courses.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Course updated.');
    }

    public function batches(): View
    {
        return view('academic.batches.index', [
            'school' => app('current_school'),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with(['course', 'academicYear'])->latest()->paginate(15),
        ]);
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', app('current_school')->id)],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'room_number' => ['nullable', 'string', 'max:255'],
        ]);

        Batch::query()->create($validated);

        return back()->with('status', 'Batch created.');
    }

    public function editBatch(Batch $batch): View
    {
        return view('academic.batches.edit', [
            'school' => app('current_school'),
            'batch' => $batch,
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
        ]);
    }

    public function updateBatch(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', app('current_school')->id)],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'room_number' => ['nullable', 'string', 'max:255'],
        ]);

        $batch->update($validated);

        return redirect()->route('tenant.batches.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Batch updated.');
    }

    public function subjects(): View
    {
        return view('academic.subjects.index', [
            'school' => app('current_school'),
            'departments' => Department::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->with('department')->latest()->paginate(15),
            'subjectOptions' => Subject::query()->orderBy('name')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderByDesc('start_date')->get(),
            'teachers' => $this->teacherUsers(),
            'assignments' => CourseSubjectAssignment::query()
                ->with(['subject', 'course', 'batch', 'teacher', 'academicYear', 'term'])
                ->latest()
                ->paginate(15, ['*'], 'assignments_page'),
        ]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'subject_type' => ['required', Rule::in(['theory', 'practical', 'elective', 'compulsory'])],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'pass_mark' => ['required', 'integer', 'min:0', 'max:100'],
            'max_mark' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        Subject::query()->create($validated);

        return back()->with('status', 'Subject created.');
    }

    public function editSubject(Subject $subject): View
    {
        return view('academic.subjects.edit', [
            'school' => app('current_school'),
            'subject' => $subject,
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where('school_id', app('current_school')->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'subject_type' => ['required', Rule::in(['theory', 'practical', 'elective', 'compulsory'])],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'pass_mark' => ['required', 'integer', 'min:0', 'max:100'],
            'max_mark' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $subject->update($validated);

        return redirect()->route('tenant.subjects.index', ['school_slug' => app('current_school')->slug])
            ->with('status', 'Subject updated.');
    }

    public function assignSubjectTeacher(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['nullable', Rule::exists('users', 'id')],
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

        CourseSubjectAssignment::query()->create($validated);

        return back()->with('status', 'Subject assigned to class/teacher.');
    }

    public function destroyAssignment(CourseSubjectAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('status', 'Assignment removed.');
    }

    private function teacherUsers()
    {
        $schoolId = app('current_school')->id;

        return User::query()
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
            ->orderBy('name')
            ->get();
    }

    private function abortIfUserOutsideSchool(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        $schoolId = app('current_school')->id;

        $belongs = User::query()
            ->where('id', $userId)
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
            ->exists();

        abort_unless($belongs, 422, 'Selected user does not belong to this school.');
    }
}
