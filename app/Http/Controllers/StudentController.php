<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Alumni;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Guardian;
use App\Models\StudentClearance;
use App\Models\StudentLifecycleEvent;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    private const CLEARANCE_DEPARTMENTS = [
        'library',
        'finance',
        'hostel',
        'transport',
        'academics',
        'administration',
    ];

    public function index(Request $request): View
    {
        $school = app('current_school');

        $students = Student::query()
            ->with(['course', 'batch'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = trim((string) $request->string('q'));
                $query->where(function ($inner) use ($search): void {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhere('student_id_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('admission_status', $request->string('status')))
            ->when($request->filled('lifecycle'), fn ($query) => $query->where('lifecycle_status', $request->string('lifecycle')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('students.index', compact('school', 'students'));
    }

    public function create(): View
    {
        $school = app('current_school');

        return view('students.create', [
            'school' => $school,
            'student' => new Student(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudent($request);
        $validated['lifecycle_status'] = $this->resolveLifecycleStatusFromAdmission($validated['admission_status'] ?? null);

        $student = DB::transaction(function () use ($request, $validated): Student {
            $student = Student::query()->create($validated);

            $this->attachPrimaryGuardian($request, $student);
            $this->storeDocuments($request, $student);

            return $student;
        });

        return redirect()
            ->route('tenant.students.show', ['school_slug' => app('current_school')->slug, 'student' => $student->id])
            ->with('status', 'Student admission created successfully.');
    }

    public function show(Student|string $student): View
    {
        if (! $student instanceof Student) {
            if (! ctype_digit($student)) {
                abort(404);
            }

            $student = Student::query()->findOrFail((int) $student);
        }

        $school = app('current_school');
        $this->ensureClearanceChecklist($student);
        $student->load([
            'course',
            'batch',
            'academicYear',
            'guardians',
            'documents',
            'clearances.clearedBy',
            'lifecycleEvents.performer',
            'alumniProfile',
        ]);

        return view('students.show', [
            'school' => $school,
            'student' => $student,
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
        ]);
    }

    public function alumni(Request $request): View
    {
        $school = app('current_school');

        $records = Alumni::query()
            ->with(['student', 'course'])
            ->when($request->filled('year'), fn ($query) => $query->where('graduation_year', (int) $request->integer('year')))
            ->orderByDesc('graduation_year')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $years = Alumni::query()
            ->select('graduation_year')
            ->distinct()
            ->orderByDesc('graduation_year')
            ->pluck('graduation_year');

        return view('students.alumni', compact('school', 'records', 'years'));
    }

    public function edit(Student $student): View
    {
        $school = app('current_school');

        return view('students.edit', [
            'school' => $school,
            'student' => $student,
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $this->validateStudent($request, $student->id);
        if (array_key_exists('admission_status', $validated) && in_array($student->lifecycle_status, ['in_progress', 'active', 'repeating'], true)) {
            $validated['lifecycle_status'] = $this->resolveLifecycleStatusFromAdmission($validated['admission_status']);
        }

        DB::transaction(function () use ($request, $student, $validated): void {
            $student->update($validated);
            $this->storeDocuments($request, $student);
        });

        return redirect()
            ->route('tenant.students.show', ['school_slug' => app('current_school')->slug, 'student' => $student->id])
            ->with('status', 'Student profile updated successfully.');
    }

    public function transition(Request $request, Student $student): RedirectResponse
    {
        $targetStatus = (string) $request->validate([
            'status' => ['required', 'in:admitted,enrolled'],
        ])['status'];

        $nextMap = [
            'applied' => 'admitted',
            'admitted' => 'enrolled',
        ];

        $expected = $nextMap[$student->admission_status] ?? null;

        if ($expected !== $targetStatus) {
            return back()->withErrors(['status' => 'Invalid status transition request.']);
        }

        if ($targetStatus === 'enrolled' && (! $student->course_id || ! $student->batch_id)) {
            return back()->withErrors(['status' => 'Assign course and batch before enrollment.']);
        }

        $payload = ['admission_status' => $targetStatus];

        if ($targetStatus === 'admitted' && ! $student->admission_date) {
            $payload['admission_date'] = now()->toDateString();
        }

        if ($targetStatus === 'enrolled' && ! $student->enrollment_date) {
            $payload['enrollment_date'] = now()->toDateString();
        }

        if ($targetStatus === 'enrolled') {
            $payload['lifecycle_status'] = 'active';
        }

        $student->update($payload);

        return back()->with('status', 'Admission status moved to ' . ucfirst($targetStatus) . '.');
    }

    public function promote(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $before = [
            'academic_year_id' => $student->academic_year_id,
            'course_id' => $student->course_id,
            'batch_id' => $student->batch_id,
        ];

        $student->update([
            'academic_year_id' => $validated['academic_year_id'],
            'course_id' => $validated['course_id'],
            'batch_id' => $validated['batch_id'] ?? null,
            'lifecycle_status' => 'active',
            'promoted_at' => now()->toDateString(),
        ]);

        $this->recordLifecycleEvent($student, 'promoted', [
            'from' => $before,
            'to' => [
                'academic_year_id' => $validated['academic_year_id'],
                'course_id' => $validated['course_id'],
                'batch_id' => $validated['batch_id'] ?? null,
            ],
        ], $validated['notes'] ?? null);

        return back()->with('status', 'Student promoted successfully.');
    }

    public function repeat(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $before = [
            'academic_year_id' => $student->academic_year_id,
            'batch_id' => $student->batch_id,
        ];

        $student->update([
            'academic_year_id' => $validated['academic_year_id'],
            'batch_id' => $validated['batch_id'] ?? $student->batch_id,
            'lifecycle_status' => 'repeating',
            'repeated_at' => now()->toDateString(),
        ]);

        $this->recordLifecycleEvent($student, 'repeated', [
            'from' => $before,
            'to' => [
                'academic_year_id' => $validated['academic_year_id'],
                'batch_id' => $validated['batch_id'] ?? $student->batch_id,
            ],
        ], $validated['notes'] ?? null);

        return back()->with('status', 'Student marked as repeating.');
    }

    public function transfer(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'transfer_destination' => ['required', 'string', 'max:255'],
            'transfer_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureClearanceChecklist($student);

        $student->update([
            'lifecycle_status' => 'transferred',
            'transferred_at' => $validated['transfer_date'] ?? now()->toDateString(),
            'transfer_destination' => $validated['transfer_destination'],
            'exit_reason' => 'transferred',
            'exit_notes' => $validated['notes'] ?? null,
        ]);

        $this->recordLifecycleEvent($student, 'transferred', [
            'transfer_destination' => $validated['transfer_destination'],
            'transfer_date' => $validated['transfer_date'] ?? null,
        ], $validated['notes'] ?? null);

        return back()->with('status', 'Student transfer has been recorded. Complete clearances to finish exit.');
    }

    public function graduate(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'graduation_year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'current_company' => ['nullable', 'string', 'max:255'],
            'current_designation' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'achievements' => ['nullable', 'string'],
            'is_visible' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->ensureClearanceChecklist($student);

        $student->update([
            'lifecycle_status' => 'graduated',
            'graduated_at' => now()->toDateString(),
            'exit_reason' => 'graduated',
            'exit_notes' => $validated['notes'] ?? null,
        ]);

        Alumni::query()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'course_id' => $student->course_id,
                'graduation_year' => $validated['graduation_year'],
                'current_company' => $validated['current_company'] ?? null,
                'current_designation' => $validated['current_designation'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'achievements' => $validated['achievements'] ?? null,
                'is_visible' => (bool) ($validated['is_visible'] ?? true),
            ]
        );

        $this->recordLifecycleEvent($student, 'graduated', [
            'graduation_year' => (int) $validated['graduation_year'],
        ], $validated['notes'] ?? null);

        return back()->with('status', 'Student graduated and added to alumni registry.');
    }

    public function initiateExit(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'exit_reason' => ['required', 'in:graduated,transferred,dropout,expelled,deceased,other'],
            'exit_date' => ['nullable', 'date'],
            'exit_notes' => ['nullable', 'string'],
        ]);

        $this->ensureClearanceChecklist($student);

        $lifecycleStatus = match ($validated['exit_reason']) {
            'graduated' => 'graduated',
            'transferred' => 'transferred',
            default => 'exited',
        };

        $student->update([
            'lifecycle_status' => $lifecycleStatus,
            'exit_reason' => $validated['exit_reason'],
            'exit_notes' => $validated['exit_notes'] ?? null,
            'exited_at' => $validated['exit_date'] ?? now()->toDateString(),
        ]);

        $this->recordLifecycleEvent($student, 'exit_initiated', [
            'exit_reason' => $validated['exit_reason'],
            'exit_date' => $validated['exit_date'] ?? null,
        ], $validated['exit_notes'] ?? null);

        return back()->with('status', 'Exit initiated. Complete departmental clearances before final completion.');
    }

    public function updateClearance(Request $request, Student $student, StudentClearance $clearance): RedirectResponse
    {
        if ($clearance->student_id !== $student->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,cleared,waived'],
            'remarks' => ['nullable', 'string'],
        ]);

        $isCleared = in_array($validated['status'], ['cleared', 'waived'], true);

        $clearance->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'cleared_by_user_id' => $isCleared ? $request->user()->id : null,
            'cleared_at' => $isCleared ? now() : null,
        ]);

        $this->recordLifecycleEvent($student, 'clearance_updated', [
            'department' => $clearance->department,
            'status' => $validated['status'],
        ], $validated['remarks'] ?? null);

        return back()->with('status', ucfirst($clearance->department) . ' clearance updated.');
    }

    public function completeExit(Request $request, Student $student): RedirectResponse
    {
        $this->ensureClearanceChecklist($student);

        $pending = $student->clearances()->where('status', 'pending')->count();
        if ($pending > 0) {
            return back()->withErrors(['clearance' => 'All departments must be cleared or waived before completing exit.']);
        }

        $student->update([
            'clearance_completed_at' => now(),
            'exited_at' => $student->exited_at ?: now()->toDateString(),
            'lifecycle_status' => in_array($student->lifecycle_status, ['graduated', 'transferred'], true)
                ? $student->lifecycle_status
                : 'exited',
        ]);

        $this->recordLifecycleEvent($student, 'exit_completed');

        return back()->with('status', 'Exit and clearance workflow completed.');
    }

    public function addGuardian(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_primary_contact' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $guardian = Guardian::query()->create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'relationship' => $validated['relationship'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_primary' => (bool) ($validated['is_primary_contact'] ?? false),
        ]);

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'relationship' => $validated['relationship'] ?? null,
                'is_primary_contact' => (bool) ($validated['is_primary_contact'] ?? false),
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        return back()->with('status', 'Guardian linked to student profile.');
    }

    private function validateStudent(Request $request, ?int $studentId = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'medical_conditions' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_school_address' => ['nullable', 'string', 'max:255'],
            'previous_school_notes' => ['nullable', 'string'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'admission_status' => ['nullable', 'in:applied,admitted,enrolled'],
            'admission_date' => ['nullable', 'date'],
            'enrollment_date' => ['nullable', 'date'],
            'student_id_number' => ['nullable', 'string', 'max:30', 'unique:students,student_id_number,' . $studentId . ',id,school_id,' . app('current_school')->id],
            'admission_number' => ['nullable', 'string', 'max:30', 'unique:students,admission_number,' . $studentId . ',id,school_id,' . app('current_school')->id],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'document_types' => ['nullable', 'array'],
            'document_types.*' => ['nullable', 'string', 'max:60'],
            'guardian_full_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function attachPrimaryGuardian(Request $request, Student $student): void
    {
        if (! $request->filled('guardian_full_name')) {
            return;
        }

        $guardian = Guardian::query()->create([
            'full_name' => (string) $request->string('guardian_full_name'),
            'phone' => $request->input('guardian_phone'),
            'email' => $request->input('guardian_email'),
            'relationship' => $request->input('guardian_relationship'),
            'occupation' => $request->input('guardian_occupation'),
            'address' => $request->input('guardian_address'),
            'is_primary' => true,
        ]);

        $student->guardians()->attach($guardian->id, [
            'relationship' => $request->input('guardian_relationship'),
            'is_primary_contact' => true,
        ]);
    }

    private function storeDocuments(Request $request, Student $student): void
    {
        $files = $request->file('documents', []);
        $types = $request->input('document_types', []);

        foreach ($files as $index => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('student-documents', 'public');

            $student->documents()->create([
                'document_type' => $types[$index] ?? 'other',
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function ensureClearanceChecklist(Student $student): void
    {
        foreach (self::CLEARANCE_DEPARTMENTS as $department) {
            StudentClearance::query()->firstOrCreate([
                'student_id' => $student->id,
                'department' => $department,
            ], [
                'status' => 'pending',
            ]);
        }
    }

    private function recordLifecycleEvent(Student $student, string $eventType, ?array $payload = null, ?string $notes = null): void
    {
        StudentLifecycleEvent::query()->create([
            'student_id' => $student->id,
            'performed_by_user_id' => auth()->id(),
            'event_type' => $eventType,
            'payload' => $payload,
            'notes' => $notes,
            'event_date' => now(),
        ]);
    }

    private function resolveLifecycleStatusFromAdmission(?string $admissionStatus): string
    {
        return $admissionStatus === 'enrolled' ? 'active' : 'in_progress';
    }
}
