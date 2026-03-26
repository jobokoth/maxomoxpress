@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Add Academic Year</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.academic-years.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" placeholder="2026/2027" required></div>
                    <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1"><label class="form-check-label" for="is_current">Set as current</label></div>
                    <button class="btn btn-primary w-100" type="submit">Create Year</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card">
            <div class="card-header"><h4 class="mb-0">Academic Years</h4></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Name</th><th>Range</th><th>Current</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse($years as $year)
                        <tr>
                            <td>{{ $year->name }}</td>
                            <td>{{ $year->start_date?->format('d M Y') }} - {{ $year->end_date?->format('d M Y') }}</td>
                            <td>{!! $year->is_current ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light-secondary text-secondary">No</span>' !!}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.academic-years.edit', ['school_slug' => $school->slug, 'academicYear' => $year->id]) }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-4">No academic years configured.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $years->links() }}</div>
        </div>
    </div>
</div>
@endsection
