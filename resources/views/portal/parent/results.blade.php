@extends('portal.layouts.app')

@section('title', 'Exam Results — ' . $student->full_name)
@section('page-title', 'Exam Results')

@section('breadcrumb')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="breadcrumb-item">
        <a href="{{ route('portal.parent.dashboard', $slug) }}" class="text-white-50">Dashboard</a>
    </li>
    <li class="breadcrumb-item text-white">{{ $student->full_name }}</li>
@endsection

@section('nav-links')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.dashboard', request()->route('school_slug')) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
@endsection

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:48px;height:48px;font-size:1.3rem;font-weight:700;">
            {{ strtoupper(substr($student->first_name, 0, 1)) }}
        </div>
        <div>
            <h6 class="mb-0 fw-bold">{{ $student->full_name }}</h6>
            <div class="text-muted small">{{ $student->batch?->name ?? '—' }} &middot; {{ $student->course?->name ?? '—' }}</div>
        </div>
    </div>
</div>

@if ($marks->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-graph-up display-4 d-block mb-2 opacity-25"></i>
            No exam results available yet.
        </div>
    </div>
@else
    @foreach ($marks as $examId => $examMarks)
        @php $exam = $examMarks->first()->exam; @endphp
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $exam?->name ?? 'Exam #'.$examId }}</span>
                @php
                    $avg = $examMarks->avg('marks_obtained');
                    $totalMax = $examMarks->sum(fn($m) => $m->schedule?->max_marks ?? 100);
                    $totalObtained = $examMarks->sum('marks_obtained');
                @endphp
                <span class="badge bg-primary">
                    {{ number_format($totalObtained, 1) }} / {{ $totalMax }}
                    &mdash; Avg {{ number_format($avg, 1) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Max Marks</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($examMarks as $mark)
                            <tr>
                                <td>{{ $mark->subject?->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ number_format($mark->marks_obtained, 1) }}</td>
                                <td class="text-muted">{{ $mark->schedule?->max_marks ?? '—' }}</td>
                                <td>
                                    @if ($mark->grade_letter)
                                        <span class="badge bg-{{ in_array($mark->grade_letter, ['A', 'A+', 'A-']) ? 'success' : (in_array($mark->grade_letter, ['F']) ? 'danger' : 'warning') }}">
                                            {{ $mark->grade_letter }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $mark->remarks ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
@endsection
