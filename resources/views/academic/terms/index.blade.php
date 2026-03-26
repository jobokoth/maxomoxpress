@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card mb-4"><div class="card-header"><h4 class="mb-0">Add Term</h4></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.terms.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-3"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" placeholder="Term 1" required></div>
                <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_current" id="term_current" value="1"><label class="form-check-label" for="term_current">Set as current</label></div>
                <button class="btn btn-primary w-100" type="submit">Create Term</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card"><div class="card-header"><h4 class="mb-0">Terms</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Academic Year</th><th>Dates</th><th>Current</th><th>Action</th></tr></thead><tbody>
            @forelse($terms as $term)
            <tr><td>{{ $term->name }}</td><td>{{ $term->academicYear?->name }}</td><td>{{ $term->start_date?->format('d M Y') }} - {{ $term->end_date?->format('d M Y') }}</td><td>{!! $term->is_current ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light-secondary text-secondary">No</span>' !!}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.terms.edit', ['school_slug' => $school->slug, 'term' => $term->id]) }}">Edit</a></td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">No terms configured.</td></tr>
            @endforelse
        </tbody></table></div><div class="card-footer">{{ $terms->links() }}</div></div>
    </div>
</div>
@endsection
