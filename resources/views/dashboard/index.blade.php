@extends('layouts.dashui')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-5 mt-4">
                <div>
                    <h3 class="mb-1">School Dashboard</h3>
                    <p class="mb-0 text-muted">Tenant: <strong>{{ $school->slug }}</strong></p>
                </div>
                <span class="badge bg-primary-subtle text-primary fs-6">
                    {{ $currentYear?->name ?? 'No current academic year' }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Users</span>
                    <h3 class="mt-3 mb-0">{{ $stats['users'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Students</span>
                    <h3 class="mt-3 mb-0">{{ $stats['students'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Departments</span>
                    <h3 class="mt-3 mb-0">{{ $stats['departments'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Courses</span>
                    <h3 class="mt-3 mb-0">{{ $stats['courses'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Subjects</span>
                    <h3 class="mt-3 mb-0">{{ $stats['subjects'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Batches</span>
                    <h3 class="mt-3 mb-0">{{ $stats['batches'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-lift h-100">
                <div class="card-body">
                    <span class="fw-semibold text-muted">Terms</span>
                    <h3 class="mt-3 mb-0">{{ $stats['terms'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-xl-6 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0">Recent Departments</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDepartments as $department)
                                <tr>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ $department->code ?: 'N/A' }}</td>
                                    <td>{{ $department->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No departments created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0">Recent Courses</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCourses as $course)
                                <tr>
                                    <td>{{ $course->name }}</td>
                                    <td>{{ $course->department?->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-light-primary text-primary">{{ ucfirst($course->course_type) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No courses created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
