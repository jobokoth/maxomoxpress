@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Teachers</h3>
                    <p class="text-muted mb-0">Manage teacher accounts and login access.</p>
                </div>
                @can('teachers.manage')
                    <a href="{{ route('tenant.teachers.create', ['school_slug' => $school->slug]) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Add Teacher
                    </a>
                @endcan
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    @if ($teachers->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-person-badge display-4 d-block mb-3 opacity-25"></i>
                            <p class="mb-2">No teachers added yet.</p>
                            <a href="{{ route('tenant.teachers.create', ['school_slug' => $school->slug]) }}" class="btn btn-outline-primary btn-sm">
                                Add your first teacher
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Joined</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teachers as $teacher)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width:36px;height:36px;font-size:.85rem;font-weight:600;">
                                                        {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $teacher->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-muted">{{ $teacher->email }}</td>
                                            <td class="text-muted">{{ $teacher->phone ?? '—' }}</td>
                                            <td class="text-muted small">{{ $teacher->pivot->joined_at ? \Carbon\Carbon::parse($teacher->pivot->joined_at)->format('d M Y') : '—' }}</td>
                                            <td class="text-end">
                                                @can('teachers.manage')
                                                    <a href="{{ route('tenant.teachers.show', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye-fill me-1"></i>View Teacher
                                                    </a>
                                                    <a href="{{ route('tenant.teachers.edit', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </a>
                                                    <form method="POST"
                                                          action="{{ route('tenant.teachers.destroy', ['school_slug' => $school->slug, 'teacher' => $teacher->id]) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Remove {{ addslashes($teacher->name) }} from this school?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-person-dash me-1"></i>Remove
                                                        </button>
                                                    </form>
                                                @endcan
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
@endsection
