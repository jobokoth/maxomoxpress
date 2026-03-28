@extends('layouts.dashui')

@section('content')
    <div class="row mt-4 justify-content-center">
        <div class="col-12 col-lg-9">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="{{ route('tenant.teachers.index', ['school_slug' => $school->slug]) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h3 class="mb-0">{{ $teacher->name }}</h3>
                    <p class="text-muted mb-0 small">Teacher Profile</p>
                </div>
                @can('teachers.manage')
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('tenant.teachers.edit', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form method="POST"
                              action="{{ route('tenant.teachers.destroy', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                              onsubmit="return confirm('Remove {{ addslashes($teacher->name) }} from this school?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-person-dash me-1"></i>Remove
                            </button>
                        </form>
                    </div>
                @endcan
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                {{-- Profile card --}}
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width:72px;height:72px;font-size:1.6rem;font-weight:700;">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                            <h5 class="mb-1">{{ $teacher->name }}</h5>
                            <span class="badge bg-primary-subtle text-primary">Teacher</span>

                            <hr>

                            <div class="text-start small">
                                <div class="mb-2 d-flex align-items-start gap-2">
                                    <i class="bi bi-envelope text-muted mt-1"></i>
                                    <span class="text-break">{{ $teacher->email }}</span>
                                </div>
                                <div class="mb-2 d-flex align-items-start gap-2">
                                    <i class="bi bi-phone text-muted mt-1"></i>
                                    <span>{{ $teacher->phone ?? '—' }}</span>
                                </div>
                                <div class="mb-2 d-flex align-items-start gap-2">
                                    <i class="bi bi-calendar2-check text-muted mt-1"></i>
                                    <span>
                                        Joined:
                                        {{ $teacher->pivot?->joined_at
                                            ? \Carbon\Carbon::parse($teacher->pivot->joined_at)->format('d M Y')
                                            : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Subject assignments --}}
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Subject Assignments</span>
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ $teacher->subjectAssignments->count() }} total
                            </span>
                        </div>
                        <div class="card-body p-0">
                            @if ($teacher->subjectAssignments->isEmpty())
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-journal-x display-6 d-block mb-2 opacity-25"></i>
                                    No subject assignments yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Class / Batch</th>
                                                <th>Academic Year</th>
                                                <th>Term</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($teacher->subjectAssignments as $assignment)
                                                <tr>
                                                    <td class="fw-semibold">{{ $assignment->subject?->name ?? '—' }}</td>
                                                    <td>
                                                        {{ $assignment->course?->name ?? '—' }}
                                                        @if ($assignment->batch)
                                                            <span class="text-muted">/ {{ $assignment->batch->name }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $assignment->academicYear?->name ?? '—' }}</td>
                                                    <td>{{ $assignment->term?->name ?? '—' }}</td>
                                                    <td>
                                                        @if ($assignment->is_active)
                                                            <span class="badge bg-success-subtle text-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
