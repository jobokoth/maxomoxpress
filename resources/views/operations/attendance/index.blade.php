@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Attendance</h3>
                <p class="text-muted mb-0">Capture daily attendance for students and staff.</p>
            </div>
        </div>

        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Attendance Date</label>
                        <input type="date" name="date" value="{{ $date }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Batch Filter (Students)</label>
                        <select name="batch_id" class="form-select">
                            <option value="">All Batches</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" @selected((string) $selectedBatchId === (string) $batch->id)>{{ $batch->name }}{{ $batch->course ? ' (' . $batch->course->name . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-outline-secondary w-100" type="submit">Load</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Student Attendance</h4></div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('tenant.operations.attendance.students.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    <input type="hidden" name="attendance_date" value="{{ $date }}">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Status</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php $record = $studentAttendanceMap->get($student->id); @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $student->full_name }}</div>
                                            <small class="text-muted">{{ $student->course?->name ?: 'No class' }} / {{ $student->batch?->name ?: 'No batch' }}</small>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" name="student_statuses[{{ $student->id }}]">
                                                @foreach(['present','absent','late','excused'] as $status)
                                                    <option value="{{ $status }}" @selected(($record?->status ?? 'present') === $status)>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-sm" name="remarks[{{ $student->id }}]" value="{{ $record?->remarks }}" placeholder="Optional">
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No students to mark.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        <button class="btn btn-primary" type="submit">Save Student Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Staff Attendance</h4></div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('tenant.operations.attendance.staff.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    <input type="hidden" name="attendance_date" value="{{ $date }}">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffUsers as $user)
                                    @php $record = $staffAttendanceMap->get($user->id); @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                            <input class="form-control form-control-sm mt-2" name="remarks[{{ $user->id }}]" value="{{ $record?->remarks }}" placeholder="Optional remark">
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" name="staff_statuses[{{ $user->id }}]">
                                                @foreach(['present','absent','late','on_leave'] as $status)
                                                    <option value="{{ $status }}" @selected(($record?->status ?? 'present') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">No staff users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        <button class="btn btn-primary" type="submit">Save Staff Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
