@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Alumni</h3>
                    <p class="text-muted mb-0">Graduated students and post-school tracking.</p>
                </div>
                <a href="{{ route('tenant.students.index', ['school_slug' => $school->slug]) }}" class="btn btn-outline-secondary">Back to Students</a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Graduation Year</label>
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected((int) request('year') === (int) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid align-items-end">
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
                                <th>Graduation Year</th>
                                <th>Course</th>
                                <th>Current Role</th>
                                <th>Visibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $record->student?->full_name ?: 'Unknown Student' }}</div>
                                        <small class="text-muted">{{ $record->student?->admission_number ?: 'No admission no' }}</small>
                                    </td>
                                    <td>{{ $record->graduation_year }}</td>
                                    <td>{{ $record->course?->name ?: 'N/A' }}</td>
                                    <td>
                                        <div>{{ $record->current_designation ?: 'N/A' }}</div>
                                        <small class="text-muted">{{ $record->current_company ?: 'No company' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $record->is_visible ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' }}">
                                            {{ $record->is_visible ? 'Visible' : 'Hidden' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No alumni records available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
