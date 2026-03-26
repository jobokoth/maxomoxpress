@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Course/Class</h4></div><div class="card-body">
    <form method="POST" action="{{ route('tenant.courses.update', ['school_slug' => $school->slug, 'course' => $course->id]) }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Department</label><select name="department_id" class="form-select" required>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id', $course->department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $course->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $course->code) }}"></div>
        <div class="mb-3"><label class="form-label">Type</label><select name="course_type" class="form-select" required>@foreach(['primary','secondary','tertiary','vocational'] as $type)<option value="{{ $type }}" @selected(old('course_type', $course->course_type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Duration (Years)</label><input type="number" min="1" max="12" name="duration_years" class="form-control" value="{{ old('duration_years', $course->duration_years) }}" required></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $course->description) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-outline-secondary" href="{{ route('tenant.courses.index', ['school_slug' => $school->slug]) }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
