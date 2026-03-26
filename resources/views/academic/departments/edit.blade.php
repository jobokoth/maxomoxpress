@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Department</h4></div><div class="card-body">
    <form method="POST" action="{{ route('tenant.departments.update', ['school_slug' => $school->slug, 'department' => $department->id]) }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $department->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $department->code) }}"></div>
        <div class="mb-3"><label class="form-label">Head of Department</label><select name="head_user_id" class="form-select"><option value="">None</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) old('head_user_id', $department->head_user_id) === (string) $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $department->description) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-outline-secondary" href="{{ route('tenant.departments.index', ['school_slug' => $school->slug]) }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
