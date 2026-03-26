@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Students</h3>
                    <p class="text-muted mb-0">Manage admissions and onboarding workflow.</p>
                </div>
                @can('students.manage')
                    <a href="{{ route('tenant.students.create', ['school_slug' => $school->slug]) }}" class="btn btn-primary">New Admission</a>
                @endcan
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, admission no, student ID">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach (['applied', 'admitted', 'enrolled'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="lifecycle" class="form-select">
                                <option value="">All Lifecycle States</option>
                                @foreach (['in_progress', 'active', 'repeating', 'transferred', 'graduated', 'exited'] as $state)
                                    <option value="{{ $state }}" @selected(request('lifecycle') === $state)>{{ ucwords(str_replace('_', ' ', $state)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-outline-secondary" type="submit">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th>Student ID</th>
                                <th>Status</th>
                                <th>Lifecycle</th>
                                <th>Class Assignment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $student->full_name }}</div>
                                        <small class="text-muted">{{ $student->email ?: 'No email' }}</small>
                                    </td>
                                    <td>{{ $student->admission_number }}</td>
                                    <td>{{ $student->student_id_number }}</td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">{{ ucfirst($student->admission_status) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-secondary text-secondary">{{ ucwords(str_replace('_', ' ', $student->lifecycle_status)) }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $student->course?->name ?: 'Not assigned' }}</div>
                                        <small class="text-muted">{{ $student->batch?->name ?: 'No batch' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('tenant.students.show', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
