@extends('portal.layouts.app')

@section('title', 'Timetable')
@section('page-title', 'My Timetable')

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.dashboard', $slug) }}"><i class="bi bi-house me-1"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('portal.student.timetable', $slug) }}"><i class="bi bi-calendar3 me-1"></i>Timetable</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.results', $slug) }}"><i class="bi bi-graph-up me-1"></i>Results</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.attendance', $slug) }}"><i class="bi bi-calendar-check me-1"></i>Attendance</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('portal.student.library', $slug) }}"><i class="bi bi-book me-1"></i>Library</a></li>
@endsection

@section('content')
@if ($currentTerm)
    <div class="alert alert-info py-2 mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Showing timetable for <strong>{{ $currentTerm->name }}</strong>
        ({{ $currentTerm->start_date?->format('d M') }} – {{ $currentTerm->end_date?->format('d M Y') }})
    </div>
@endif

@if (! $student->batch_id)
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-calendar3 display-4 d-block mb-2 opacity-25"></i>
            You are not assigned to a class yet.
        </div>
    </div>
@elseif ($timetable->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-calendar3 display-4 d-block mb-2 opacity-25"></i>
            No timetable entries for the current term.
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($days as $day)
            @if (isset($timetable[$day]) && $timetable[$day]->isNotEmpty())
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white fw-semibold">
                            <i class="bi bi-calendar-day me-2"></i>{{ $day }}
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach ($timetable[$day] as $entry)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $entry->subject?->name ?? '—' }}</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-person me-1"></i>{{ $entry->teacher?->name ?? '—' }}
                                            </div>
                                            @if ($entry->room)
                                                <div class="text-muted small">
                                                    <i class="bi bi-geo me-1"></i>Room {{ $entry->room }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end text-muted small text-nowrap">
                                            {{ $entry->start_time }}<br>{{ $entry->end_time }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
