@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    @if (session('status'))<div class="col-12"><div class="alert alert-success">{{ session('status') }}</div></div>@endif
    @if ($errors->any())<div class="col-12"><div class="alert alert-danger">{{ $errors->first() }}</div></div>@endif

    @can('subjects.manage')
    <div class="col-lg-4">
        <div class="card mb-4"><div class="card-header"><h4 class="mb-0">Add Subject</h4></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.subjects.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-3"><label class="form-label">Department</label><select name="department_id" class="form-select" required>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Type</label><select name="subject_type" class="form-select" required>@foreach(['theory','practical','elective','compulsory'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Credit Hours</label><input type="number" min="1" max="24" name="credit_hours" class="form-control" value="3" required></div>
                <div class="row g-2"><div class="col"><label class="form-label">Pass Mark</label><input type="number" min="0" max="100" name="pass_mark" class="form-control" value="40" required></div><div class="col"><label class="form-label">Max Mark</label><input type="number" min="1" max="1000" name="max_mark" class="form-control" value="100" required></div></div>
                <div class="mb-3 mt-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <button class="btn btn-primary w-100" type="submit">Create Subject</button>
            </form>
        </div></div>
    </div>
    @endcan

    <div class="{{ auth()->user()->can('subjects.manage') ? 'col-lg-8' : 'col-lg-12' }}">
        <div class="card mb-4"><div class="card-header"><h4 class="mb-0">Subjects</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Subject</th><th>Department</th><th>Type</th><th>Marks</th><th>Action</th></tr></thead><tbody>
            @forelse($subjects as $subject)
                <tr>
                    <td>{{ $subject->name }} <small class="text-muted">{{ $subject->code }}</small></td>
                    <td>{{ $subject->department?->name }}</td>
                    <td>{{ ucfirst($subject->subject_type) }}</td>
                    <td>{{ $subject->pass_mark }}/{{ $subject->max_mark }}</td>
                    <td>
                        @can('subjects.manage')
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.subjects.edit', ['school_slug' => $school->slug, 'subject' => $subject->id]) }}">Edit</a>
                        @else
                            <span class="text-muted">View only</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No subjects configured.</td></tr>
            @endforelse
        </tbody></table></div><div class="card-footer">{{ $subjects->links() }}</div></div>
    </div>

    @can('teacher-assignments.manage')
    <div class="col-12">
        <div class="card mb-4"><div class="card-header"><h4 class="mb-0">Assign Subject to Class + Teacher</h4></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.subject-assignments.store', ['school_slug' => $school->slug]) }}" class="row g-3">@csrf
                <div class="col-md-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select" required>@foreach($subjectOptions as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Course/Class</label><select name="course_id" class="form-select" required>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Batch/Section</label><select name="batch_id" class="form-select"><option value="">All Batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Teacher</label><select name="teacher_user_id" class="form-select"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select"><option value="">Any</option>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Term</label><select name="term_id" class="form-select"><option value="">Any</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Notes</label><input name="notes" class="form-control" placeholder="Optional notes"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Assign</button></div>
            </form>
        </div></div>

        <div class="card"><div class="card-header"><h4 class="mb-0">Current Assignments</h4></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Subject</th><th>Class</th><th>Batch</th><th>Teacher</th><th>Year/Term</th><th>Action</th></tr></thead><tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->subject?->name }}</td>
                    <td>{{ $assignment->course?->name }}</td>
                    <td>{{ $assignment->batch?->name ?: 'All' }}</td>
                    <td>{{ $assignment->teacher?->name ?: 'Unassigned' }}</td>
                    <td>{{ $assignment->academicYear?->name ?: 'Any' }} / {{ $assignment->term?->name ?: 'Any' }}</td>
                    <td>
                        <form method="POST" action="{{ route('tenant.subject-assignments.destroy', ['school_slug' => $school->slug, 'assignment' => $assignment->id]) }}">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No subject assignments yet.</td></tr>
            @endforelse
        </tbody></table></div><div class="card-footer">{{ $assignments->links() }}</div></div>
    </div>
    @endcan
</div>
@endsection
