<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Course;
use App\Models\FeeAssignment;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $school = app('current_school');

        $filters = $request->validate([
            'course_id' => ['nullable', 'integer'],
            'batch_id' => ['nullable', 'integer'],
            'student_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['pending', 'partial', 'paid', 'waived', 'overdue'])],
        ]);

        $assignmentsQuery = FeeAssignment::query()->with([
            'student.course',
            'student.batch',
            'structure.category',
            'academicYear',
            'term',
        ]);

        if (! empty($filters['student_id'])) {
            $assignmentsQuery->where('student_id', (int) $filters['student_id']);
        } elseif (! empty($filters['course_id']) || ! empty($filters['batch_id'])) {
            $assignmentsQuery->whereHas('student', function ($query) use ($filters): void {
                if (! empty($filters['course_id'])) {
                    $query->where('course_id', (int) $filters['course_id']);
                }
                if (! empty($filters['batch_id'])) {
                    $query->where('batch_id', (int) $filters['batch_id']);
                }
            });
        }

        if (! empty($filters['status'])) {
            $assignmentsQuery->where('status', $filters['status']);
        }

        $assignments = $assignmentsQuery->latest()->paginate(20, ['*'], 'assignments_page');

        $payments = FeePayment::query()
            ->with(['student', 'assignment', 'collectedBy'])
            ->latest('payment_date')
            ->latest()
            ->paginate(20, ['*'], 'payments_page');

        $totals = [
            'invoiced' => (float) FeeAssignment::query()->sum('final_amount'),
            'collected' => (float) FeePayment::query()->sum('amount_paid'),
            'balance' => (float) FeeAssignment::query()->sum('balance_amount'),
            'arrears' => (float) FeeAssignment::query()
                ->where('balance_amount', '>', 0)
                ->whereDate('due_date', '<', now()->toDateString())
                ->sum('balance_amount'),
        ];

        $arrearsList = FeeAssignment::query()
            ->with(['student.course', 'student.batch', 'structure'])
            ->where('balance_amount', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->limit(30)
            ->get();

        return view('finance.index', [
            'school' => $school,
            'categories' => FeeCategory::query()->orderBy('name')->get(),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderByDesc('start_date')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'students' => Student::query()->orderBy('last_name')->orderBy('first_name')->get(),
            'structures' => FeeStructure::query()
                ->with(['category', 'academicYear', 'term', 'course', 'batch'])
                ->latest()
                ->paginate(20, ['*'], 'structures_page'),
            'assignments' => $assignments,
            'payments' => $payments,
            'arrearsList' => $arrearsList,
            'totals' => $totals,
            'filters' => [
                'courseId' => (int) ($filters['course_id'] ?? 0),
                'batchId' => (int) ($filters['batch_id'] ?? 0),
                'studentId' => (int) ($filters['student_id'] ?? 0),
                'status' => $filters['status'] ?? '',
            ],
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_mandatory' => ['nullable', 'boolean'],
        ]);

        FeeCategory::query()->create($validated);

        return back()->with('status', 'Fee category created.');
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'fee_category_id' => ['required', Rule::exists('fee_categories', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'frequency' => ['required', Rule::in(['once', 'monthly', 'quarterly', 'annually'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        FeeStructure::query()->create($validated);

        return back()->with('status', 'Fee structure created.');
    }

    public function generateInvoices(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
            'due_date' => ['nullable', 'date'],
            'scholarship_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'adjustment_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $structures = FeeStructure::query()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('course_id', $validated['course_id'])
            ->when(! empty($validated['term_id']), fn ($query) => $query->where('term_id', $validated['term_id']))
            ->when(! empty($validated['batch_id']), function ($query) use ($validated): void {
                $query->where(function ($inner) use ($validated): void {
                    $inner->whereNull('batch_id')->orWhere('batch_id', $validated['batch_id']);
                });
            })
            ->where('is_active', true)
            ->get();

        if ($structures->isEmpty()) {
            throw ValidationException::withMessages([
                'course_id' => 'No active fee structures found for selected filters.',
            ]);
        }

        $students = Student::query()
            ->where('course_id', $validated['course_id'])
            ->when(! empty($validated['batch_id']), fn ($query) => $query->where('batch_id', $validated['batch_id']))
            ->get();

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'course_id' => 'No students found for selected class/batch.',
            ]);
        }

        $baseScholarship = (float) ($validated['scholarship_amount'] ?? 0);
        $baseDiscount = (float) ($validated['discount_amount'] ?? 0);
        $baseFine = (float) ($validated['fine_amount'] ?? 0);

        DB::transaction(function () use ($students, $structures, $validated, $baseScholarship, $baseDiscount, $baseFine, $schoolId): void {
            foreach ($students as $student) {
                foreach ($structures as $structure) {
                    $assignment = FeeAssignment::query()->updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'fee_structure_id' => $structure->id,
                            'academic_year_id' => $validated['academic_year_id'],
                            'term_id' => $validated['term_id'] ?? null,
                        ],
                        [
                            'school_id' => $schoolId,
                            'amount' => $structure->amount,
                            'scholarship_amount' => $baseScholarship,
                            'discount_amount' => $baseDiscount,
                            'fine_amount' => $baseFine,
                            'due_date' => $validated['due_date'] ?? $structure->due_date,
                            'adjustment_reason' => $validated['adjustment_reason'] ?? null,
                            'notes' => $validated['notes'] ?? null,
                        ]
                    );

                    $this->recalculateAssignment($assignment);
                }
            }
        });

        return back()->with('status', 'Invoices generated successfully.');
    }

    public function updateAssignmentAdjustments(Request $request, FeeAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'scholarship_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'adjustment_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $assignment->update([
            'scholarship_amount' => (float) ($validated['scholarship_amount'] ?? 0),
            'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
            'fine_amount' => (float) ($validated['fine_amount'] ?? 0),
            'due_date' => $validated['due_date'] ?? $assignment->due_date,
            'adjustment_reason' => $validated['adjustment_reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->recalculateAssignment($assignment);

        return back()->with('status', 'Invoice adjustments updated.');
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'fee_assignment_id'  => ['required', Rule::exists('fee_assignments', 'id')->where('school_id', $schoolId)],
            'amount_paid'        => ['required', 'numeric', 'gt:0'],
            'payment_date'       => ['required', 'date'],
            'payment_method'     => ['required', Rule::in(['cash', 'bank', 'bank_transfer', 'card', 'online', 'cheque', 'mpesa'])],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
            // Mpesa manual entry
            'mpesa_receipt_no'   => ['nullable', 'string', 'max:30'],
            'mpesa_phone'        => ['nullable', 'string', 'max:20'],
            // Bank transfer
            'bank_transfer_type' => ['nullable', Rule::in(['rtgs', 'swift', 'pesalink'])],
            'bank_transfer_ref'  => ['nullable', 'string', 'max:100'],
            // Cheque
            'cheque_number'      => ['nullable', 'string', 'max:50'],
            'cheque_bank'        => ['nullable', 'string', 'max:100'],
            'cheque_date'        => ['nullable', 'date'],
        ]);

        $assignment = FeeAssignment::query()->with('student')->findOrFail($validated['fee_assignment_id']);

        $amount = (float) $validated['amount_paid'];
        $balance = (float) $assignment->balance_amount;
        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Amount paid cannot exceed current balance.',
            ]);
        }

        FeePayment::query()->create([
            'school_id'              => $schoolId,
            'student_id'             => $assignment->student_id,
            'fee_assignment_id'      => $assignment->id,
            'amount_paid'            => $amount,
            'payment_date'           => $validated['payment_date'],
            'payment_method'         => $validated['payment_method'],
            'transaction_reference'  => $validated['transaction_reference'] ?? null,
            'receipt_number'         => $this->generateReceiptNumber(),
            'collected_by_user_id'   => $request->user()?->id,
            'notes'                  => $validated['notes'] ?? null,
            // Mpesa
            'mpesa_receipt_no'       => $validated['mpesa_receipt_no'] ?? null,
            'mpesa_phone'            => $validated['mpesa_phone'] ?? null,
            // Bank transfer
            'bank_transfer_type'     => $validated['bank_transfer_type'] ?? null,
            'bank_transfer_ref'      => $validated['bank_transfer_ref'] ?? null,
            // Cheque
            'cheque_number'          => $validated['cheque_number'] ?? null,
            'cheque_bank'            => $validated['cheque_bank'] ?? null,
            'cheque_date'            => $validated['cheque_date'] ?? null,
        ]);

        $this->recalculateAssignment($assignment->fresh());

        return back()->with('status', 'Payment recorded and receipt generated.');
    }

    /** Public wrapper so MpesaWebhookController can trigger recalculation. */
    public function recalculateAssignmentPublic(FeeAssignment $assignment): void
    {
        $this->recalculateAssignment($assignment);
    }

    public function receipt(FeePayment $payment): View
    {
        return view('finance.receipt', [
            'school' => app('current_school'),
            'payment' => $payment->load(['student.course', 'student.batch', 'assignment.structure', 'collectedBy']),
        ]);
    }

    public function receiptPdf(FeePayment $payment): Response
    {
        $payment->load(['student.course', 'student.batch', 'assignment.structure', 'collectedBy']);
        $pdf = Pdf::loadView('finance.pdf.receipt', [
            'school' => app('current_school'),
            'payment' => $payment,
        ]);

        return $pdf->download("receipt-{$payment->receipt_number}.pdf");
    }

    public function statement(Request $request, Student $student): View
    {
        $schoolId = app('current_school')->id;
        $filters = $request->validate([
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
        ]);

        $statement = $this->buildStatementData(
            $student,
            isset($filters['academic_year_id']) ? (int) $filters['academic_year_id'] : null,
            isset($filters['term_id']) ? (int) $filters['term_id'] : null
        );

        return view('finance.statement', [
            'school' => app('current_school'),
            'student' => $student->load(['course', 'batch']),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderByDesc('start_date')->get(),
            'filters' => [
                'academicYearId' => (int) ($filters['academic_year_id'] ?? 0),
                'termId' => (int) ($filters['term_id'] ?? 0),
            ],
            ...$statement,
        ]);
    }

    public function statementPdf(Request $request, Student $student): Response
    {
        $schoolId = app('current_school')->id;
        $filters = $request->validate([
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
        ]);

        $statement = $this->buildStatementData(
            $student,
            isset($filters['academic_year_id']) ? (int) $filters['academic_year_id'] : null,
            isset($filters['term_id']) ? (int) $filters['term_id'] : null
        );

        $pdf = Pdf::loadView('finance.pdf.statement', [
            'school' => app('current_school'),
            'student' => $student->load(['course', 'batch']),
            'filters' => [
                'academicYearId' => (int) ($filters['academic_year_id'] ?? 0),
                'termId' => (int) ($filters['term_id'] ?? 0),
            ],
            ...$statement,
        ]);

        $admissionNo = $student->admission_number ?: ('student-' . $student->id);

        return $pdf->download("statement-{$admissionNo}.pdf");
    }

    private function recalculateAssignment(FeeAssignment $assignment): void
    {
        $base = (float) $assignment->amount;
        $scholarship = (float) $assignment->scholarship_amount;
        $discount = (float) $assignment->discount_amount;
        $fine = (float) $assignment->fine_amount;

        $finalAmount = max($base - $scholarship - $discount + $fine, 0);
        $paidAmount = (float) FeePayment::query()->where('fee_assignment_id', $assignment->id)->sum('amount_paid');
        $balanceAmount = max($finalAmount - $paidAmount, 0);

        $status = $this->resolveAssignmentStatus($finalAmount, $paidAmount, $balanceAmount, $assignment->due_date);

        $assignment->update([
            'final_amount' => $finalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'status' => $status,
        ]);
    }

    private function resolveAssignmentStatus(float $finalAmount, float $paidAmount, float $balanceAmount, $dueDate): string
    {
        if ($finalAmount <= 0) {
            return 'waived';
        }

        if ($balanceAmount <= 0) {
            return 'paid';
        }

        $isOverdue = $dueDate && Carbon::parse($dueDate)->isPast();

        if ($isOverdue) {
            return 'overdue';
        }

        if ($paidAmount > 0) {
            return 'partial';
        }

        return 'pending';
    }

    private function generateReceiptNumber(): string
    {
        $prefix = 'RCP-' . now()->format('Ymd');

        $last = FeePayment::query()
            ->where('receipt_number', 'like', $prefix . '-%')
            ->latest('id')
            ->value('receipt_number');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $next = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%04d', $prefix, $next);
    }

    private function buildStatementData(Student $student, ?int $academicYearId = null, ?int $termId = null): array
    {
        $assignments = FeeAssignment::query()
            ->with(['structure.category', 'academicYear', 'term', 'payments'])
            ->where('student_id', $student->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->when($termId, fn ($query) => $query->where('term_id', $termId))
            ->latest('due_date')
            ->latest()
            ->get();

        $payments = FeePayment::query()
            ->with(['assignment.structure', 'collectedBy'])
            ->where('student_id', $student->id)
            ->when($academicYearId, function ($query) use ($academicYearId): void {
                $query->whereHas('assignment', fn ($inner) => $inner->where('academic_year_id', $academicYearId));
            })
            ->when($termId, function ($query) use ($termId): void {
                $query->whereHas('assignment', fn ($inner) => $inner->where('term_id', $termId));
            })
            ->latest('payment_date')
            ->latest()
            ->get();

        return [
            'assignments' => $assignments,
            'payments' => $payments,
            'summary' => [
                'invoiced' => (float) $assignments->sum('final_amount'),
                'paid' => (float) $payments->sum('amount_paid'),
                'balance' => (float) $assignments->sum('balance_amount'),
                'overdue' => (float) $assignments
                    ->where('status', 'overdue')
                    ->sum('balance_amount'),
            ],
        ];
    }
}
