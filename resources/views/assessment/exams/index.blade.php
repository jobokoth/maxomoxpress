@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Create Exam</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.assessment.exams.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label">Type</label><select name="exam_type" class="form-select form-select-sm" required>@foreach(['quiz','cat','midterm','endterm','practical','mock','final'] as $type)<option value="{{ $type }}">{{ strtoupper($type) }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select form-select-sm"><option value="">None</option>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Term</label><select name="term_id" class="form-select form-select-sm"><option value="">None</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control form-control-sm"></div><div class="col"><label class="form-label">End</label><input type="date" name="end_date" class="form-control form-control-sm"></div></div>
                    <div class="mb-2"><label class="form-label">Description</label><input name="description" class="form-control form-control-sm"></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_published" id="exam_published" value="1"><label class="form-check-label" for="exam_published">Published</label></div>
                    <button class="btn btn-primary w-100" type="submit">Create Exam</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Create Exam Schedule</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.assessment.schedules.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    <div class="mb-2"><label class="form-label">Exam</label><select name="exam_id" class="form-select form-select-sm" required>@foreach($exams as $exam)<option value="{{ $exam->id }}">{{ $exam->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Course</label><select name="course_id" class="form-select form-select-sm" required>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><option value="">All batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Subject</label><select name="subject_id" class="form-select form-select-sm" required>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Date</label><input type="date" name="exam_date" class="form-control form-control-sm"></div><div class="col"><label class="form-label">Start</label><input type="time" name="start_time" class="form-control form-control-sm"></div><div class="col"><label class="form-label">End</label><input type="time" name="end_time" class="form-control form-control-sm"></div></div>
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Total Marks</label><input type="number" name="total_marks" class="form-control form-control-sm" value="100" min="1" required></div><div class="col"><label class="form-label">Pass Marks</label><input type="number" name="pass_marks" class="form-control form-control-sm" value="40" min="0" required></div></div>
                    <div class="mb-2"><label class="form-label">Invigilator</label><select name="invigilator_user_id" class="form-select form-select-sm"><option value="">None</option>@foreach($staffUsers as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Notes</label><input name="notes" class="form-control form-control-sm"></div>
                    <button class="btn btn-primary w-100" type="submit">Create Schedule</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Exams</h4></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Type</th><th>Year/Term</th><th>Dates</th><th>Action</th></tr></thead><tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exam->name }}</td>
                        <td>{{ strtoupper($exam->exam_type) }}</td>
                        <td>{{ $exam->academicYear?->name ?: 'N/A' }} / {{ $exam->term?->name ?: 'N/A' }}</td>
                        <td>{{ $exam->start_date?->format('d M Y') ?: '-' }} - {{ $exam->end_date?->format('d M Y') ?: '-' }}</td>
                        <td>
                            <details><summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                <form class="mt-2" method="POST" action="{{ route('tenant.assessment.exams.update', ['school_slug' => $school->slug, 'exam' => $exam->id]) }}">@csrf @method('PUT')
                                    <input name="name" class="form-control form-control-sm mb-1" value="{{ $exam->name }}" required>
                                    <select name="exam_type" class="form-select form-select-sm mb-1" required>@foreach(['quiz','cat','midterm','endterm','practical','mock','final'] as $type)<option value="{{ $type }}" @selected($exam->exam_type === $type)>{{ strtoupper($type) }}</option>@endforeach</select>
                                    <div class="row g-1 mb-1"><div class="col"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $exam->start_date?->format('Y-m-d') }}"></div><div class="col"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $exam->end_date?->format('Y-m-d') }}"></div></div>
                                    <input name="description" class="form-control form-control-sm mb-1" value="{{ $exam->description }}">
                                    <input type="hidden" name="academic_year_id" value="{{ $exam->academic_year_id }}"><input type="hidden" name="term_id" value="{{ $exam->term_id }}">
                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No exams created.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $exams->links() }}</div>
        </div>

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Exam Schedules</h4></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Exam</th><th>Class/Subject</th><th>Date/Time</th><th>Marks</th><th>Action</th></tr></thead><tbody>
                @forelse($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->exam?->name }}</td>
                        <td>{{ $schedule->course?->name }} {{ $schedule->batch?->name ? '/ '.$schedule->batch?->name : '' }}<br><small class="text-muted">{{ $schedule->subject?->name }}</small></td>
                        <td>{{ $schedule->exam_date?->format('d M Y') ?: '-' }}<br><small class="text-muted">{{ $schedule->start_time ? substr($schedule->start_time,0,5) : '-' }} - {{ $schedule->end_time ? substr($schedule->end_time,0,5) : '-' }}</small></td>
                        <td>{{ $schedule->total_marks }} / Pass {{ $schedule->pass_marks }}</td>
                        <td>
                            <details><summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                <form class="mt-2" method="POST" action="{{ route('tenant.assessment.schedules.update', ['school_slug' => $school->slug, 'schedule' => $schedule->id]) }}">@csrf @method('PUT')
                                    <input type="hidden" name="exam_id" value="{{ $schedule->exam_id }}"><input type="hidden" name="course_id" value="{{ $schedule->course_id }}"><input type="hidden" name="batch_id" value="{{ $schedule->batch_id }}"><input type="hidden" name="subject_id" value="{{ $schedule->subject_id }}">
                                    <div class="row g-1 mb-1"><div class="col"><input type="date" name="exam_date" class="form-control form-control-sm" value="{{ $schedule->exam_date?->format('Y-m-d') }}"></div><div class="col"><input type="time" name="start_time" class="form-control form-control-sm" value="{{ $schedule->start_time }}"></div><div class="col"><input type="time" name="end_time" class="form-control form-control-sm" value="{{ $schedule->end_time }}"></div></div>
                                    <div class="row g-1 mb-1"><div class="col"><input type="number" name="total_marks" class="form-control form-control-sm" value="{{ $schedule->total_marks }}"></div><div class="col"><input type="number" name="pass_marks" class="form-control form-control-sm" value="{{ $schedule->pass_marks }}"></div></div>
                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No schedules created.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $schedules->links() }}</div>
        </div>
    </div>
</div>
@endsection
