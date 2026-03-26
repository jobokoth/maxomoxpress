@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4"><div class="card mb-4"><div class="card-header"><h4 class="mb-0">Add Course/Class</h4></div><div class="card-body">
        <form method="POST" action="{{ route('tenant.courses.store', ['school_slug' => $school->slug]) }}">@csrf
            <div class="mb-3"><label class="form-label">Department</label><select name="department_id" class="form-select" required>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Type</label><select name="course_type" class="form-select" required>@foreach(['primary','secondary','tertiary','vocational'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Duration (Years)</label><input type="number" min="1" max="12" name="duration_years" class="form-control" value="1" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary w-100" type="submit">Create Course</button>
        </form>
    </div></div></div>
    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card"><div class="card-header"><h4 class="mb-0">Courses / Classes</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Department</th><th>Type</th><th>Duration</th><th>Action</th></tr></thead><tbody>
        @forelse($courses as $course)
            <tr><td>{{ $course->name }} <small class="text-muted">{{ $course->code }}</small></td><td>{{ $course->department?->name }}</td><td>{{ ucfirst($course->course_type) }}</td><td>{{ $course->duration_years }} yr</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.courses.edit', ['school_slug' => $school->slug, 'course' => $course->id]) }}">Edit</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No courses found.</td></tr>
        @endforelse
        </tbody></table></div><div class="card-footer">{{ $courses->links() }}</div></div>
    </div>
</div>
@endsection
