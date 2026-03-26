@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Batch/Section</h4></div><div class="card-body">
    <form method="POST" action="{{ route('tenant.batches.update', ['school_slug' => $school->slug, 'batch' => $batch->id]) }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select" required>@foreach($years as $year)<option value="{{ $year->id }}" @selected((string) old('academic_year_id', $batch->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Course/Class</label><select name="course_id" class="form-select" required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((string) old('course_id', $batch->course_id) === (string) $course->id)>{{ $course->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Batch Name</label><input name="name" class="form-control" value="{{ old('name', $batch->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Capacity</label><input type="number" min="1" max="500" name="capacity" class="form-control" value="{{ old('capacity', $batch->capacity) }}" required></div>
        <div class="mb-3"><label class="form-label">Room Number</label><input name="room_number" class="form-control" value="{{ old('room_number', $batch->room_number) }}"></div>
        <button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-outline-secondary" href="{{ route('tenant.batches.index', ['school_slug' => $school->slug]) }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
