@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card mb-4"><div class="card-header"><h4 class="mb-0">Select Exam Schedule</h4></div><div class="card-body">
            <form method="GET">
                <label class="form-label">Schedule</label>
                <select name="schedule_id" class="form-select mb-3" required>
                    <option value="">Choose schedule</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->id }}" @selected((int) request('schedule_id') === $schedule->id)>
                            {{ $schedule->exam?->name }} | {{ $schedule->course?->name }}{{ $schedule->batch?->name ? ' / '.$schedule->batch?->name : '' }} | {{ $schedule->subject?->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-outline-secondary w-100" type="submit">Load Students</button>
            </form>
        </div></div>

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Add Grading Rule</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.assessment.grading-rules.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><label class="form-label">Scale Name</label><input name="name" class="form-control form-control-sm" value="Default" required></div>
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Min</label><input type="number" step="0.01" name="min_mark" class="form-control form-control-sm" required></div><div class="col"><label class="form-label">Max</label><input type="number" step="0.01" name="max_mark" class="form-control form-control-sm" required></div></div>
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Grade</label><input name="grade_letter" class="form-control form-control-sm" required></div><div class="col"><label class="form-label">Point</label><input type="number" step="0.01" name="grade_point" class="form-control form-control-sm" required></div></div>
                    <div class="mb-2"><label class="form-label">Remarks</label><input name="remarks" class="form-control form-control-sm"></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1"><label class="form-check-label" for="is_default">Set as default</label></div>
                    <button class="btn btn-primary w-100" type="submit">Add Rule</button>
                </form>

                <hr>
                <h6>Current Rules</h6>
                @forelse($gradingRules as $rule)
                    <details class="small border rounded p-2 mb-2">
                        <summary>
                            {{ $rule->name }}: {{ $rule->min_mark }} - {{ $rule->max_mark }} = <strong>{{ $rule->grade_letter }}</strong> ({{ $rule->grade_point }})
                            @if($rule->is_default)<span class="badge bg-success ms-1">Default</span>@endif
                        </summary>
                        <form class="mt-2" method="POST" action="{{ route('tenant.assessment.grading-rules.update', ['school_slug' => $school->slug, 'gradingRule' => $rule->id]) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-2"><input name="name" class="form-control form-control-sm" value="{{ $rule->name }}" required></div>
                            <div class="row g-2 mb-2"><div class="col"><input type="number" step="0.01" name="min_mark" class="form-control form-control-sm" value="{{ $rule->min_mark }}" required></div><div class="col"><input type="number" step="0.01" name="max_mark" class="form-control form-control-sm" value="{{ $rule->max_mark }}" required></div></div>
                            <div class="row g-2 mb-2"><div class="col"><input name="grade_letter" class="form-control form-control-sm" value="{{ $rule->grade_letter }}" required></div><div class="col"><input type="number" step="0.01" name="grade_point" class="form-control form-control-sm" value="{{ $rule->grade_point }}" required></div></div>
                            <div class="mb-2"><input name="remarks" class="form-control form-control-sm" value="{{ $rule->remarks }}" placeholder="Remarks"></div>
                            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="default_rule_{{ $rule->id }}" @checked($rule->is_default)><label class="form-check-label" for="default_rule_{{ $rule->id }}">Set as default</label></div>
                            <button class="btn btn-sm btn-primary" type="submit">Update</button>
                        </form>
                        <form class="mt-2" method="POST" action="{{ route('tenant.assessment.grading-rules.destroy', ['school_slug' => $school->slug, 'gradingRule' => $rule->id]) }}" onsubmit="return confirm('Delete this grading rule?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </details>
                @empty
                    <p class="text-muted small mb-0">No grading rules yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Marks Entry {{ $selectedSchedule ? '- '.$selectedSchedule->subject?->name : '' }}</h4></div>
            <div class="card-body p-0">
                @if($selectedSchedule)
                    <div class="px-3 py-2 border-bottom small text-muted">
                        Total Marks: <strong>{{ $selectedSchedule->total_marks }}</strong> |
                        Pass Marks: <strong>{{ $selectedSchedule->pass_marks }}</strong> |
                        Date: <strong>{{ $selectedSchedule->exam_date?->format('d M Y') ?: 'Not set' }}</strong>
                    </div>
                    <form method="POST" action="{{ route('tenant.assessment.marks.store', ['school_slug' => $school->slug]) }}">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead><tr><th>Student</th><th>Admission</th><th>Marks</th><th>Grade</th><th>Remarks</th></tr></thead>
                                <tbody>
                                    @forelse($students as $student)
                                        @php $record = $marksByStudent->get($student->id); @endphp
                                        <tr>
                                            <td>{{ $student->full_name }}</td>
                                            <td>{{ $student->admission_number }}</td>
                                            <td><input type="number" step="0.01" min="0" max="{{ $selectedSchedule->total_marks }}" name="marks[{{ $student->id }}]" class="form-control form-control-sm" value="{{ $record?->marks_obtained }}"></td>
                                            <td>{{ $record?->grade_letter ?: '-' }}</td>
                                            <td><input name="remarks[{{ $student->id }}]" class="form-control form-control-sm" value="{{ $record?->remarks }}"></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No students in selected class/batch.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top">
                            <button class="btn btn-primary" type="submit">Save Marks</button>
                        </div>
                    </form>
                @else
                    <div class="p-4 text-muted">Select an exam schedule to begin marks entry.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
