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
                <div class="ms-auto">
                    <form method="POST"
                          action="{{ route('tenant.teachers.destroy', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                          class="d-inline"
                          onsubmit="return confirm('Remove {{ addslashes($teacher->name) }} from this school?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-person-dash me-1"></i>Remove Teacher
                        </button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                {{-- Profile summary --}}
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width:72px;height:72px;font-size:1.6rem;font-weight:700;">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                            <h5 class="mb-1">{{ $teacher->name }}</h5>
                            <span class="badge bg-primary-subtle text-primary mb-3">Teacher</span>

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
                                        {{ $teacher->pivot->joined_at
                                            ? \Carbon\Carbon::parse($teacher->pivot->joined_at)->format('d M Y')
                                            : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit form --}}
                <div class="col-12 col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <span class="fw-semibold">Edit Details</span>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST"
                                  action="{{ route('tenant.teachers.update', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $teacher->name) }}"
                                               required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control bg-light"
                                               value="{{ $teacher->email }}" disabled readonly>
                                        <div class="form-text">Email cannot be changed here.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone"
                                               class="form-control"
                                               value="{{ old('phone', $teacher->phone) }}"
                                               placeholder="e.g. +254712345678">
                                    </div>

                                    <div class="col-12"><hr class="my-1"></div>
                                    <p class="text-muted small mb-0">Leave password fields blank to keep the current password.</p>

                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Min 8 characters">
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="password_confirmation"
                                               class="form-control"
                                               placeholder="Repeat new password">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('tenant.teachers.index', ['school_slug' => $school->slug]) }}"
                                       class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-lg me-1"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Subject assignments --}}
                    <div class="card shadow-sm mt-4">
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
                                                <th>Year / Term</th>
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
                                                    <td>{{ $assignment->academicYear?->name ?? '—' }} {{ $assignment->term?->name ? '· '.$assignment->term->name : '' }}</td>
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
