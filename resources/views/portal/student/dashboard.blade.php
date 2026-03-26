@extends('portal.layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'My Dashboard')

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('portal.student.dashboard', $slug) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.student.timetable', $slug) }}">
            <i class="bi bi-calendar3 me-1"></i>Timetable
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.student.results', $slug) }}">
            <i class="bi bi-graph-up me-1"></i>Results
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.student.attendance', $slug) }}">
            <i class="bi bi-calendar-check me-1"></i>Attendance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.student.library', $slug) }}">
            <i class="bi bi-book me-1"></i>Library
        </a>
    </li>
@endsection

@section('content')
{{-- Student info card --}}
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:64px;height:64px;font-size:1.8rem;font-weight:700;">
            {{ strtoupper(substr($student->first_name, 0, 1)) }}
        </div>
        <div>
            <h5 class="mb-0 fw-bold">{{ $student->full_name }}</h5>
            <div class="text-muted small">
                {{ $student->course?->name ?? '—' }} &middot; {{ $student->batch?->name ?? '—' }}
            </div>
            <div class="text-muted small">
                Admission No: <strong>{{ $student->admission_number }}</strong>
                &middot; {{ $student->academicYear?->name ?? '—' }}
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Fee balance --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#ede9fe;color:#6d28d9;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="small text-muted">Fee Balance</div>
                <div class="fw-bold {{ ($feeSummary->total_balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $school->currency ?? 'KES' }} {{ number_format($feeSummary->total_balance ?? 0, 2) }}
                </div>
            </div>
        </div>
    </div>
    {{-- Attendance rate --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#d1fae5;color:#065f46;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="small text-muted">Attendance (30d)</div>
                <div class="fw-bold {{ $attendanceSummary['rate'] >= 75 ? 'text-success' : 'text-danger' }}">
                    {{ $attendanceSummary['rate'] }}%
                </div>
            </div>
        </div>
    </div>
    {{-- Books on loan --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#dbeafe;color:#1e40af;">
                    <i class="bi bi-book"></i>
                </div>
                <div class="small text-muted">Books on Loan</div>
                <div class="fw-bold">{{ $activeBook->count() }}</div>
            </div>
        </div>
    </div>
    {{-- Class --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#fef3c7;color:#92400e;">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div class="small text-muted">Current Class</div>
                <div class="fw-bold small">{{ $student->batch?->name ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Announcements --}}
    <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold bg-white">
                <i class="bi bi-megaphone me-2 text-warning"></i>Announcements
            </div>
            <div class="list-group list-group-flush">
                @forelse ($announcements as $ann)
                    <div class="list-group-item">
                        <div class="fw-semibold small text-truncate">{{ $ann->title }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $ann->published_at?->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center small py-3">No announcements.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Events --}}
    <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold bg-white">
                <i class="bi bi-calendar-event me-2 text-info"></i>Upcoming Events
            </div>
            <div class="list-group list-group-flush">
                @forelse ($events as $event)
                    <div class="list-group-item">
                        <div class="fw-semibold small text-truncate">{{ $event->title }}</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $event->start_at?->format('d M Y') }}
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center small py-3">No upcoming events.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Books on loan --}}
    @if ($activeBook->isNotEmpty())
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header fw-semibold bg-white">
                    <i class="bi bi-book me-2 text-warning"></i>Books Currently on Loan
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($activeBook as $issue)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $issue->book?->title ?? '—' }}</div>
                                <div class="text-muted small">Issued: {{ $issue->issued_at?->format('d M Y') }}</div>
                            </div>
                            @if ($issue->due_date)
                                <span class="badge {{ $issue->due_date->isPast() ? 'bg-danger' : 'bg-secondary' }}">
                                    Due {{ $issue->due_date->format('d M') }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
