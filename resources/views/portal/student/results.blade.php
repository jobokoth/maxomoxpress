@extends('portal.layouts.app')

@section('title', 'My Results')
@section('page-title', 'Exam Results')

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.dashboard', $slug) }}"><i class="bi bi-house me-1"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.timetable', $slug) }}"><i class="bi bi-calendar3 me-1"></i>Timetable</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('portal.student.results', $slug) }}"><i class="bi bi-graph-up me-1"></i>Results</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.attendance', $slug) }}"><i class="bi bi-calendar-check me-1"></i>Attendance</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.library', $slug) }}"><i class="bi bi-book me-1"></i>Library</a></li>
@endsection

@section('content')
@if ($marks->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-graph-up display-4 d-block mb-2 opacity-25"></i>
            No exam results available yet.
        </div>
    </div>
@else
    @foreach ($marks as $examId => $examMarks)
        @php
            $exam = $examMarks->first()->exam;
            $totalObtained = $examMarks->sum('marks_obtained');
            $totalMax = $examMarks->sum(fn($m) => $m->schedule?->max_marks ?? 100);
            $pct = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0;
        @endphp
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $exam?->name ?? 'Exam' }}</span>
                <span class="badge bg-primary fs-6">{{ $pct }}%</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Max</th>
                            <th>%</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($examMarks as $mark)
                            @php
                                $max = $mark->schedule?->max_marks ?? 100;
                                $p = $max > 0 ? round(($mark->marks_obtained / $max) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>{{ $mark->subject?->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ number_format($mark->marks_obtained, 1) }}</td>
                                <td class="text-muted">{{ $max }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                            <div class="progress-bar {{ $p >= 50 ? 'bg-success' : 'bg-danger' }}"
                                                 style="width:{{ $p }}%"></div>
                                        </div>
                                        <span class="small text-muted">{{ $p }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($mark->grade_letter)
                                        <span class="badge bg-{{ in_array($mark->grade_letter, ['A', 'A+', 'A-']) ? 'success' : (str_starts_with($mark->grade_letter, 'F') ? 'danger' : 'warning text-dark') }}">
                                            {{ $mark->grade_letter }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format($totalObtained, 1) }}</td>
                            <td>{{ $totalMax }}</td>
                            <td>{{ $pct }}%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
@endif
@endsection
