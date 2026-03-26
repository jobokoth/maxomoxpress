@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Subject</h4></div><div class="card-body">
    <form method="POST" action="{{ route('tenant.subjects.update', ['school_slug' => $school->slug, 'subject' => $subject->id]) }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Department</label><select name="department_id" class="form-select" required>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id', $subject->department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $subject->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $subject->code) }}"></div>
        <div class="mb-3"><label class="form-label">Type</label><select name="subject_type" class="form-select" required>@foreach(['theory','practical','elective','compulsory'] as $type)<option value="{{ $type }}" @selected(old('subject_type', $subject->subject_type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
        <div class="row g-2"><div class="col"><label class="form-label">Credit Hours</label><input type="number" min="1" max="24" name="credit_hours" class="form-control" value="{{ old('credit_hours', $subject->credit_hours) }}" required></div><div class="col"><label class="form-label">Pass Mark</label><input type="number" min="0" max="100" name="pass_mark" class="form-control" value="{{ old('pass_mark', $subject->pass_mark) }}" required></div><div class="col"><label class="form-label">Max Mark</label><input type="number" min="1" max="1000" name="max_mark" class="form-control" value="{{ old('max_mark', $subject->max_mark) }}" required></div></div>
        <div class="mb-3 mt-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $subject->description) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-outline-secondary" href="{{ route('tenant.subjects.index', ['school_slug' => $school->slug]) }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
