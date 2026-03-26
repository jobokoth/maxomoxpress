<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Course;
use App\Models\FeeAssignment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentMark;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceReportController extends Controller
{
    public function index(Request $request): View
    {
        $school = app('current_school');
        $filters = $this->validatedFilters($request);
        $reports = $this->buildReports($filters);

        return view('compliance.index', [
            'school' => $school,
            'filters' => $filters,
            'courses' => Course::query()->orderBy('name')->get(),
            'batches' => Batch::query()->with('course')->orderBy('name')->get(),
            'reports' => $reports,
            'auditLogs' => AuditLog::query()->with('user')->latest()->take(30)->get(),
        ]);
    }

    public function exportPdf(Request $request, string $report): \Symfony\Component\HttpFoundation\Response
    {
        $filters = $this->validatedFilters($request);
        $dataset = $this->resolveReport($report, $filters);
        $this->logAudit('compliance.report.export.pdf', $report, $filters, $dataset['rows']->count(), $request);

        $pdf = Pdf::loadView('compliance.pdf.report', [
            'school' => app('current_school'),
            'report' => $dataset,
            'filters' => $filters,
            'generatedAt' => now(),
            'generatedBy' => $request->user()?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->reportFilename($report, 'pdf'));
    }

    public function exportExcel(Request $request, string $report): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $dataset = $this->resolveReport($report, $filters);
        $this->logAudit('compliance.report.export.excel', $report, $filters, $dataset['rows']->count(), $request);

        $filename = $this->reportFilename($report, 'csv');

        return Response::streamDownload(function () use ($dataset): void {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            fputcsv($handle, $dataset['columns']);
            foreach ($dataset['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $schoolId = app('current_school')->id;

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('school_id', $schoolId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('school_id', $schoolId)],
        ]);

        $filters['date_from'] = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $filters['date_to'] = $filters['date_to'] ?? now()->toDateString();
        $filters['course_id'] = isset($filters['course_id']) ? (int) $filters['course_id'] : null;
        $filters['batch_id'] = isset($filters['batch_id']) ? (int) $filters['batch_id'] : null;

        return $filters;
    }

    private function buildReports(array $filters): array
    {
        return [
            'enrollment' => $this->enrollmentReport($filters),
            'attendance' => $this->attendanceReport($filters),
            'performance' => $this->performanceReport($filters),
            'fees' => $this->feeReport($filters),
        ];
    }

    private function resolveReport(string $report, array $filters): array
    {
        return match ($report) {
            'enrollment' => $this->enrollmentReport($filters),
            'attendance' => $this->attendanceReport($filters),
            'performance' => $this->performanceReport($filters),
            'fees' => $this->feeReport($filters),
            default => abort(404),
        };
    }

    private function enrollmentReport(array $filters): array
    {
        $students = Student::query()
            ->with(['course', 'batch'])
            ->where(function (Builder $query) use ($filters): void {
                $query->whereBetween('admission_date', [$filters['date_from'], $filters['date_to']])
                    ->orWhereBetween('enrollment_date', [$filters['date_from'], $filters['date_to']]);
            });

        $students = $this->applyStudentFilters($students, $filters)
            ->orderBy('admission_date')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = $students->map(fn (Student $student): array => [
            $student->admission_number ?: '-',
            $student->full_name,
            $student->course?->name ?: '-',
            $student->batch?->name ?: '-',
            $student->admission_date?->format('Y-m-d') ?: '-',
            $student->enrollment_date?->format('Y-m-d') ?: '-',
            strtoupper((string) $student->admission_status),
        ]);

        return [
            'key' => 'enrollment',
            'title' => 'Enrollment Report',
            'columns' => ['Admission #', 'Student', 'Course', 'Batch', 'Admission Date', 'Enrollment Date', 'Status'],
            'rows' => $rows,
            'summary' => [
                'Total Records' => $rows->count(),
                'Applied' => $students->where('admission_status', 'applied')->count(),
                'Admitted' => $students->where('admission_status', 'admitted')->count(),
                'Enrolled' => $students->where('admission_status', 'enrolled')->count(),
            ],
        ];
    }

    private function attendanceReport(array $filters): array
    {
        $records = StudentAttendance::query()
            ->with(['student.course', 'student.batch'])
            ->whereBetween('attendance_date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['course_id'], fn (Builder $query) => $query->where('course_id', $filters['course_id']))
            ->when($filters['batch_id'], fn (Builder $query) => $query->where('batch_id', $filters['batch_id']))
            ->get()
            ->groupBy('student_id');

        $rows = $records->map(function (Collection $items): array {
            $student = $items->first()?->student;
            $total = $items->count();
            $present = $items->where('status', 'present')->count();
            $late = $items->where('status', 'late')->count();
            $absent = $items->where('status', 'absent')->count();
            $excused = $items->where('status', 'excused')->count();
            $rate = $total > 0 ? (($present + $late + $excused) / $total) * 100 : 0;

            return [
                $student?->admission_number ?: '-',
                $student?->full_name ?: '-',
                $student?->course?->name ?: '-',
                $student?->batch?->name ?: '-',
                $present,
                $late,
                $excused,
                $absent,
                $total,
                number_format($rate, 2) . '%',
            ];
        })->values();

        $allEntries = $records->flatten(1);
        $totalEntries = $allEntries->count();

        return [
            'key' => 'attendance',
            'title' => 'Attendance Compliance Report',
            'columns' => ['Admission #', 'Student', 'Course', 'Batch', 'Present', 'Late', 'Excused', 'Absent', 'Total Days', 'Attendance Rate'],
            'rows' => $rows,
            'summary' => [
                'Students Tracked' => $rows->count(),
                'Total Entries' => $totalEntries,
                'Overall Present' => $allEntries->where('status', 'present')->count(),
                'Overall Absent' => $allEntries->where('status', 'absent')->count(),
            ],
        ];
    }

    private function performanceReport(array $filters): array
    {
        $marks = StudentMark::query()
            ->with(['student.course', 'student.batch'])
            ->whereBetween('created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ])
            ->whereHas('student', function (Builder $query) use ($filters): void {
                if ($filters['course_id']) {
                    $query->where('course_id', $filters['course_id']);
                }
                if ($filters['batch_id']) {
                    $query->where('batch_id', $filters['batch_id']);
                }
            })
            ->get()
            ->groupBy('student_id');

        $rows = $marks->map(function (Collection $items): array {
            $student = $items->first()?->student;
            $avgMarks = $items->avg(fn (StudentMark $mark) => (float) $mark->marks_obtained);
            $avgGradePoint = $items->avg(fn (StudentMark $mark) => (float) ($mark->grade_point ?? 0));

            return [
                $student?->admission_number ?: '-',
                $student?->full_name ?: '-',
                $student?->course?->name ?: '-',
                $student?->batch?->name ?: '-',
                $items->count(),
                number_format((float) $avgMarks, 2),
                number_format((float) $avgGradePoint, 2),
                $items->sortByDesc('created_at')->first()?->grade_letter ?: '-',
            ];
        })->values();

        $flat = $marks->flatten(1);

        return [
            'key' => 'performance',
            'title' => 'Performance Report',
            'columns' => ['Admission #', 'Student', 'Course', 'Batch', 'Assessments', 'Average Marks', 'Average Grade Point', 'Latest Grade'],
            'rows' => $rows,
            'summary' => [
                'Students Evaluated' => $rows->count(),
                'Assessments Captured' => $flat->count(),
                'Average Marks (Overall)' => number_format((float) $flat->avg(fn (StudentMark $mark) => (float) $mark->marks_obtained), 2),
                'Average GPA (Overall)' => number_format((float) $flat->avg(fn (StudentMark $mark) => (float) ($mark->grade_point ?? 0)), 2),
            ],
        ];
    }

    private function feeReport(array $filters): array
    {
        $assignments = FeeAssignment::query()
            ->with(['student.course', 'student.batch'])
            ->whereBetween('created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ])
            ->whereHas('student', function (Builder $query) use ($filters): void {
                if ($filters['course_id']) {
                    $query->where('course_id', $filters['course_id']);
                }
                if ($filters['batch_id']) {
                    $query->where('batch_id', $filters['batch_id']);
                }
            })
            ->get()
            ->groupBy('student_id');

        $rows = $assignments->map(function (Collection $items): array {
            $student = $items->first()?->student;
            $invoiced = (float) $items->sum('final_amount');
            $paid = (float) $items->sum('paid_amount');
            $balance = (float) $items->sum('balance_amount');
            $overdue = $items->where('status', 'overdue')->count();

            return [
                $student?->admission_number ?: '-',
                $student?->full_name ?: '-',
                $student?->course?->name ?: '-',
                $student?->batch?->name ?: '-',
                $items->count(),
                number_format($invoiced, 2),
                number_format($paid, 2),
                number_format($balance, 2),
                $overdue,
            ];
        })->values();

        $flat = $assignments->flatten(1);

        return [
            'key' => 'fees',
            'title' => 'Fee Compliance Report',
            'columns' => ['Admission #', 'Student', 'Course', 'Batch', 'Invoices', 'Invoiced', 'Paid', 'Balance', 'Overdue Invoices'],
            'rows' => $rows,
            'summary' => [
                'Students Billed' => $rows->count(),
                'Invoices' => $flat->count(),
                'Total Invoiced' => number_format((float) $flat->sum('final_amount'), 2),
                'Total Collected' => number_format((float) $flat->sum('paid_amount'), 2),
                'Total Balance' => number_format((float) $flat->sum('balance_amount'), 2),
            ],
        ];
    }

    private function applyStudentFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['course_id'], fn (Builder $builder) => $builder->where('course_id', $filters['course_id']))
            ->when($filters['batch_id'], fn (Builder $builder) => $builder->where('batch_id', $filters['batch_id']));
    }

    private function reportFilename(string $report, string $extension): string
    {
        return sprintf('compliance-%s-%s.%s', $report, now()->format('Ymd-His'), $extension);
    }

    private function logAudit(string $action, string $reportType, array $filters, int $records, Request $request): void
    {
        $format = str_contains($action, '.pdf') ? 'PDF' : 'EXCEL';

        AuditLog::query()->create([
            'school_id' => app('current_school')->id,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => 'compliance_report',
            'description' => sprintf('%s export for %s report', $format, $reportType),
            'details' => [
                'report_type' => $reportType,
                'filters' => $filters,
                'record_count' => $records,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }
}
