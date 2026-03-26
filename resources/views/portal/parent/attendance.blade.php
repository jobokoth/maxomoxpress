@extends('portal.layouts.app')

@section('title', 'Attendance — ' . $student->full_name)
@section('page-title', 'Attendance Record')

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
            <div class="text-muted small">{{ $student->batch?->name ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-3">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fw-bold fs-4 text-success">{{ $summary['present'] }}</div>
                <div class="small text-muted">Present</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fw-bold fs-4 text-danger">{{ $summary['absent'] }}</div>
                <div class="small text-muted">Absent</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fw-bold fs-4 text-warning">{{ $summary['late'] }}</div>
                <div class="small text-muted">Late</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-3">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fw-bold fs-4 {{ $summary['rate'] >= 75 ? 'text-success' : 'text-danger' }}">
                    {{ $summary['rate'] }}%
                </div>
                <div class="small text-muted">Rate</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header fw-semibold bg-white">Last 90 Days</div>
    @if ($attendance->isEmpty())
        <div class="card-body text-muted text-center py-5">No attendance records found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendance as $record)
                        <tr>
                            <td>{{ $record->attendance_date?->format('D, d M Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $record->status }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
