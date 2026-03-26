@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4"><div class="card mb-4"><div class="card-header"><h4 class="mb-0">Add Department</h4></div><div class="card-body">
        <form method="POST" action="{{ route('tenant.departments.store', ['school_slug' => $school->slug]) }}">@csrf
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Head of Department</label><select name="head_user_id" class="form-select"><option value="">None</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary w-100" type="submit">Create Department</button>
        </form>
    </div></div></div>
    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card"><div class="card-header"><h4 class="mb-0">Departments</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Code</th><th>Head</th><th>Action</th></tr></thead><tbody>
            @forelse($departments as $department)
                <tr><td>{{ $department->name }}</td><td>{{ $department->code ?: 'N/A' }}</td><td>{{ $department->head?->name ?: 'N/A' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.departments.edit', ['school_slug' => $school->slug, 'department' => $department->id]) }}">Edit</a></td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No departments found.</td></tr>
            @endforelse
        </tbody></table></div><div class="card-footer">{{ $departments->links() }}</div></div>
    </div>
</div>
@endsection
