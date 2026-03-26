@extends('portal.layouts.app')

@section('title', 'Library')
@section('page-title', 'Library')

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.dashboard', $slug) }}"><i class="bi bi-house me-1"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.timetable', $slug) }}"><i class="bi bi-calendar3 me-1"></i>Timetable</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.results', $slug) }}"><i class="bi bi-graph-up me-1"></i>Results</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.attendance', $slug) }}"><i class="bi bi-calendar-check me-1"></i>Attendance</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('portal.student.library', $slug) }}"><i class="bi bi-book me-1"></i>Library</a></li>
@endsection

@section('content')
{{-- Currently borrowed --}}
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold bg-white">
        <i class="bi bi-book-half me-2 text-warning"></i>Currently Borrowed
        <span class="badge bg-warning text-dark ms-2">{{ $currentIssues->count() }}</span>
    </div>
    @if ($currentIssues->isEmpty())
        <div class="card-body text-muted text-center py-4">No books currently on loan.</div>
    @else
        <div class="list-group list-group-flush">
            @foreach ($currentIssues as $issue)
                <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">{{ $issue->book?->title ?? '—' }}</div>
                        <div class="text-muted small">
                            {{ $issue->book?->author ?? '' }}
                            @if ($issue->book?->isbn) &middot; ISBN: {{ $issue->book->isbn }} @endif
                        </div>
                        <div class="text-muted small">Issued: {{ $issue->issued_at?->format('d M Y') }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        @if ($issue->due_date)
                            <span class="badge {{ $issue->due_date->isPast() ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $issue->due_date->isPast() ? 'Overdue' : 'Due' }}
                                {{ $issue->due_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- History --}}
<div class="card shadow-sm">
    <div class="card-header fw-semibold bg-white">
        <i class="bi bi-clock-history me-2 text-secondary"></i>Return History
    </div>
    @if ($history->isEmpty())
        <div class="card-body text-muted text-center py-4">No return history.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Book</th>
                        <th>Issued</th>
                        <th>Returned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $issue)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $issue->book?->title ?? '—' }}</div>
                                <div class="text-muted small">{{ $issue->book?->author }}</div>
                            </td>
                            <td class="small">{{ $issue->issued_at?->format('d M Y') }}</td>
                            <td class="small text-success">{{ $issue->returned_at?->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
