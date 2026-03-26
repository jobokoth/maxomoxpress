<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    public function exams(): View
    {
        $school = app('current_school');

        return view('assessment.exams.index', [
            'school' => $school,
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'staffUsers' => User::query()->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))->orderBy('name')->get(),
            'exams' => Exam::query()->with(['academicYear', 'term'])->latest()->paginate(15),
            'schedules' => ExamSchedule::query()->with(['exam', 'course', 'batch', 'subject', 'invigilator'])->latest()->paginate(20, ['*'], 'schedules_page'),
        ]);
    }

    public function storeExam(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['required', Rule::in(['quiz', 'cat', 'midterm', 'endterm', 'practical', 'mock', 'final'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_published' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        Exam::query()->create($validated);

        return back()->with('status', 'Exam created.');
    }

    public function updateExam(Request $request, Exam $exam): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['required', Rule::in(['quiz', 'cat', 'midterm', 'endterm', 'practical', 'mock', 'final'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_published' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $exam->update($validated);

        return back()->with('status', 'Exam updated.');
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'exam_id' => ['required', Rule::exists('exams', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'exam_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'total_marks' => ['required', 'integer', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'integer', 'min:0', 'max:1000', 'lte:total_marks'],
            'invigilator_user_id' => ['nullable', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ]);

        $this->validateSchoolUser($validated['invigilator_user_id'] ?? null);

        ExamSchedule::query()->create($validated);

        return back()->with('status', 'Exam schedule created.');
    }

    public function updateSchedule(Request $request, ExamSchedule $schedule): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'exam_id' => ['required', Rule::exists('exams', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'exam_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'total_marks' => ['required', 'integer', 'min:1', 'max:1000'],
            'pass_marks' => ['required', 'integer', 'min:0', 'max:1000', 'lte:total_marks'],
            'invigilator_user_id' => ['nullable', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ]);

        $this->validateSchoolUser($validated['invigilator_user_id'] ?? null);

        $schedule->update($validated);

        return back()->with('status', 'Exam schedule updated.');
    }

    public function marks(Request $request): View
    {
        $school = app('current_school');

        $scheduleId = $request->integer('schedule_id');
        $selectedSchedule = $scheduleId ? ExamSchedule::query()->with(['exam', 'course', 'batch', 'subject'])->find($scheduleId) : null;

        $students = collect();
        $marksByStudent = collect();

        if ($selectedSchedule) {
            $students = Student::query()
                ->where('course_id', $selectedSchedule->course_id)
                ->when($selectedSchedule->batch_id, fn ($query) => $query->where('batch_id', $selectedSchedule->batch_id))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $marksByStudent = StudentMark::query()
                ->where('exam_schedule_id', $selectedSchedule->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('assessment.marks.index', [
            'school' => $school,
            'selectedSchedule' => $selectedSchedule,
            'schedules' => ExamSchedule::query()->with(['exam', 'course', 'batch', 'subject'])->latest()->get(),
            'students' => $students,
            'marksByStudent' => $marksByStudent,
            'gradingRules' => GradingScale::query()->orderByDesc('is_default')->orderBy('min_mark')->get(),
        ]);
    }

    public function storeGradingRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_mark' => ['required', 'numeric', 'min:0', 'max:1000'],
            'max_mark' => ['required', 'numeric', 'min:0', 'max:1000', 'gte:min_mark'],
            'grade_letter' => ['required', 'string', 'max:8'],
            'grade_point' => ['required', 'numeric', 'min:0', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $this->assertNoOverlappingRule((float) $validated['min_mark'], (float) $validated['max_mark']);

        if ((bool) ($validated['is_default'] ?? false)) {
            GradingScale::query()->update(['is_default' => false]);
        }

        GradingScale::query()->create($validated);

        return back()->with('status', 'Grading rule added.');
    }

    public function updateGradingRule(Request $request, GradingScale $gradingRule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_mark' => ['required', 'numeric', 'min:0', 'max:1000'],
            'max_mark' => ['required', 'numeric', 'min:0', 'max:1000', 'gte:min_mark'],
            'grade_letter' => ['required', 'string', 'max:8'],
            'grade_point' => ['required', 'numeric', 'min:0', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $this->assertNoOverlappingRule((float) $validated['min_mark'], (float) $validated['max_mark'], $gradingRule->id);

        if ((bool) ($validated['is_default'] ?? false)) {
            GradingScale::query()->update(['is_default' => false]);
        }

        $gradingRule->update($validated);

        return back()->with('status', 'Grading rule updated.');
    }

    public function destroyGradingRule(GradingScale $gradingRule): RedirectResponse
    {
        $gradingRule->delete();

        return back()->with('status', 'Grading rule deleted.');
    }

    public function storeMarks(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'schedule_id' => ['required', Rule::exists('exam_schedules', 'id')->where('school_id', $schoolId)],
            'marks' => ['required', 'array'],
            'marks.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $schedule = ExamSchedule::query()->findOrFail($validated['schedule_id']);
        $gradingRules = GradingScale::query()->orderBy('min_mark')->get();
        $eligibleStudentIds = Student::query()
            ->where('course_id', $schedule->course_id)
            ->when($schedule->batch_id, fn ($query) => $query->where('batch_id', $schedule->batch_id))
            ->pluck('id')
            ->all();

        foreach ($validated['marks'] as $studentId => $markValue) {
            if ($markValue === null || $markValue === '') {
                continue;
            }

            if ((float) $markValue > (float) $schedule->total_marks) {
                throw ValidationException::withMessages([
                    "marks.$studentId" => "Marks cannot exceed total marks ({$schedule->total_marks}) for this paper.",
                ]);
            }

            if (! in_array((int) $studentId, $eligibleStudentIds, true)) {
                throw ValidationException::withMessages([
                    "marks.$studentId" => 'Selected student does not belong to this schedule class/batch.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $schedule, $gradingRules, $request, $schoolId): void {
            foreach ($validated['marks'] as $studentId => $markValue) {
                if ($markValue === null || $markValue === '') {
                    continue;
                }

                $student = Student::query()
                    ->where('id', $studentId)
                    ->where('course_id', $schedule->course_id)
                    ->when($schedule->batch_id, fn ($query) => $query->where('batch_id', $schedule->batch_id))
                    ->first();

                if (! $student) {
                    continue;
                }

                [$gradeLetter, $gradePoint] = $this->resolveGrade((float) $markValue, $gradingRules);

                StudentMark::query()->updateOrCreate(
                    [
                        'exam_schedule_id' => $schedule->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'school_id' => $schoolId,
                        'exam_id' => $schedule->exam_id,
                        'subject_id' => $schedule->subject_id,
                        'marks_obtained' => $markValue,
                        'grade_letter' => $gradeLetter,
                        'grade_point' => $gradePoint,
                        'remarks' => $validated['remarks'][$studentId] ?? null,
                        'entered_by_user_id' => $request->user()?->id,
                    ]
                );
            }
        });

        return back()->with('status', 'Marks saved successfully.');
    }

    public function reports(Request $request): View
    {
        $school = app('current_school');

        $filters = $request->validate([
            'exam_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'batch_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'integer'],
        ]);

        $examId = $filters['exam_id'] ?? null;
        $courseId = $filters['course_id'] ?? null;
        $batchId = $filters['batch_id'] ?? null;
        $studentId = $filters['student_id'] ?? null;

        $marksQuery = StudentMark::query()->with(['student', 'exam', 'schedule', 'subject']);

        if ($examId) {
            $marksQuery->where('exam_id', $examId);
        }

        if ($studentId) {
            $marksQuery->where('student_id', $studentId);
        } elseif ($courseId || $batchId) {
            $marksQuery->whereHas('student', function ($query) use ($courseId, $batchId): void {
                if ($courseId) {
                    $query->where('course_id', $courseId);
                }
                if ($batchId) {
                    $query->where('batch_id', $batchId);
                }
            });
        }

        $marks = $marksQuery->get();

        $reportRows = $marks
            ->groupBy('student_id')
            ->map(fn (Collection $studentMarks) => $this->buildReportRow($studentMarks))
            ->sortByDesc('percentage')
            ->values();

        return view('assessment.reports.index', [
            'school' => $school,
            'exams' => Exam::query()->latest()->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'students' => Student::query()->orderBy('last_name')->get(),
            'reportRows' => $reportRows,
            'filters' => [
                'examId' => (int) ($examId ?? 0),
                'courseId' => (int) ($courseId ?? 0),
                'batchId' => (int) ($batchId ?? 0),
                'studentId' => (int) ($studentId ?? 0),
            ],
        ]);
    }

    private function resolveGrade(float $mark, $gradingRules): array
    {
        $rule = $gradingRules->first(function (GradingScale $scale) use ($mark): bool {
            return $mark >= (float) $scale->min_mark && $mark <= (float) $scale->max_mark;
        });

        if (! $rule) {
            return ['N/A', null];
        }

        return [$rule->grade_letter, (float) $rule->grade_point];
    }

    private function gradeForAverage(float $average): string
    {
        $gradingRules = GradingScale::query()->orderBy('min_mark')->get();
        [$grade, ] = $this->resolveGrade($average, $gradingRules);

        return $grade;
    }

    private function buildReportRow(Collection $studentMarks): array
    {
        $orderedTranscript = $studentMarks
            ->sortBy([
                fn (StudentMark $record) => $record->exam?->start_date?->timestamp ?? 0,
                fn (StudentMark $record) => $record->schedule?->exam_date?->timestamp ?? 0,
                fn (StudentMark $record) => $record->subject?->name ?? '',
            ])
            ->values();

        $obtainedMarks = $orderedTranscript->sum('marks_obtained');
        $possibleMarks = max((float) $orderedTranscript->sum(fn (StudentMark $record) => (float) ($record->schedule?->total_marks ?? 100)), 1);
        $percentage = round(($obtainedMarks / $possibleMarks) * 100, 2);
        $gpa = round((float) $orderedTranscript->whereNotNull('grade_point')->avg('grade_point'), 2);
        $passCount = $orderedTranscript->filter(function (StudentMark $record): bool {
            $passMarks = (float) ($record->schedule?->pass_marks ?? 0);

            return (float) $record->marks_obtained >= $passMarks;
        })->count();

        return [
            'student' => $orderedTranscript->first()->student,
            'subjects_count' => $orderedTranscript->count(),
            'obtained_marks' => $obtainedMarks,
            'possible_marks' => $possibleMarks,
            'percentage' => $percentage,
            'grade' => $this->gradeForAverage($percentage),
            'gpa' => is_nan($gpa) ? 0 : $gpa,
            'pass_count' => $passCount,
            'fail_count' => max($orderedTranscript->count() - $passCount, 0),
            'transcript' => $orderedTranscript,
        ];
    }

    private function assertNoOverlappingRule(float $minMark, float $maxMark, ?int $ignoreRuleId = null): void
    {
        $query = GradingScale::query()
            ->where(function ($builder) use ($minMark, $maxMark): void {
                $builder
                    ->whereBetween('min_mark', [$minMark, $maxMark])
                    ->orWhereBetween('max_mark', [$minMark, $maxMark])
                    ->orWhere(function ($nested) use ($minMark, $maxMark): void {
                        $nested->where('min_mark', '<=', $minMark)->where('max_mark', '>=', $maxMark);
                    });
            });

        if ($ignoreRuleId) {
            $query->where('id', '!=', $ignoreRuleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'min_mark' => 'This grade range overlaps an existing grading rule.',
            ]);
        }
    }

    private function validateSchoolUser(?int $userId): void
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
