@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4"><div class="card mb-4"><div class="card-header"><h4 class="mb-0">Add Batch/Section</h4></div><div class="card-body">
        <form method="POST" action="{{ route('tenant.batches.store', ['school_slug' => $school->slug]) }}">@csrf
            <div class="mb-3"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Course/Class</label><select name="course_id" class="form-select" required>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Batch Name</label><input name="name" class="form-control" placeholder="Form 1 East" required></div>
            <div class="mb-3"><label class="form-label">Capacity</label><input type="number" min="1" max="500" name="capacity" class="form-control" value="45" required></div>
            <div class="mb-3"><label class="form-label">Room Number</label><input name="room_number" class="form-control"></div>
            <button class="btn btn-primary w-100" type="submit">Create Batch</button>
        </form>
    </div></div></div>
    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card"><div class="card-header"><h4 class="mb-0">Batches / Sections</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Course</th><th>Year</th><th>Capacity</th><th>Action</th></tr></thead><tbody>
        @forelse($batches as $batch)
            <tr><td>{{ $batch->name }}</td><td>{{ $batch->course?->name }}</td><td>{{ $batch->academicYear?->name }}</td><td>{{ $batch->capacity }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.batches.edit', ['school_slug' => $school->slug, 'batch' => $batch->id]) }}">Edit</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No batches found.</td></tr>
        @endforelse
        </tbody></table></div><div class="card-footer">{{ $batches->links() }}</div></div>
    </div>
</div>
@endsection
