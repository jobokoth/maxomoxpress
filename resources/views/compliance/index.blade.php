@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    </div>

    <div class="col-12">
        <h4 class="mb-0">Compliance & Reports</h4>
        <p class="text-muted mb-0">Enrollment, attendance, performance, and fee compliance with export and audit logs.</p>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Filters</h5></div>
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-3"><label class="form-label">From</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}"></div>
                    <div class="col-md-3"><label class="form-label">To</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}"></div>
                    <div class="col-md-3"><label class="form-label">Course</label><select name="course_id" class="form-select form-select-sm"><option value="">All</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((int)($filters['course_id'] ?? 0) === $course->id)>{{ $course->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><option value="">All</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" @selected((int)($filters['batch_id'] ?? 0) === $batch->id)>{{ $batch->name }} ({{ $batch->course?->name }})</option>@endforeach</select></div>
                    <div class="col-12 d-grid d-md-flex justify-content-end">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($reports as $report)
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $report['title'] }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tenant.compliance.reports.export.pdf', ['school_slug' => $school->slug, 'report' => $report['key']] + request()->query()) }}" class="btn btn-sm btn-outline-danger">Export PDF</a>
                        <a href="{{ route('tenant.compliance.reports.export.excel', ['school_slug' => $school->slug, 'report' => $report['key']] + request()->query()) }}" class="btn btn-sm btn-outline-success">Export Excel (CSV)</a>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        @foreach($report['summary'] as $label => $value)
                            <div class="col-md-3">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block">{{ $label }}</small>
                                    <strong>{{ $value }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                @foreach($report['columns'] as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['rows']->take(20) as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($report['columns']) }}" class="text-muted text-center py-3">No records for selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($report['rows']->count() > 20)
                    <div class="card-footer"><small class="text-muted">Showing first 20 of {{ $report['rows']->count() }} rows. Use export for full dataset.</small></div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Audit Logs</h5></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>Details</th></tr></thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->user?->name ?: 'System' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->description ?: '-' }}</td>
                                <td><small class="text-muted">{{ json_encode($log->details) }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center py-3">No audit logs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
