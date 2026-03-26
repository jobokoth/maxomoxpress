@php
    $entry = $entry ?? null;
    $prefix = $entry ? 'entry_' . $entry->id . '_' : 'new_';
@endphp

<div class="mb-2">
    <label class="form-label">Academic Year</label>
    <select name="academic_year_id" class="form-select form-select-sm">
        <option value="">Any</option>
        @foreach($years as $year)
            <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $entry?->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Term</label>
    <select name="term_id" class="form-select form-select-sm">
        <option value="">Any</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}" @selected((string) old('term_id', $entry?->term_id) === (string) $term->id)>{{ $term->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Course/Class</label>
    <select name="course_id" class="form-select form-select-sm" required>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" @selected((string) old('course_id', $entry?->course_id) === (string) $course->id)>{{ $course->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Batch/Section</label>
    <select name="batch_id" class="form-select form-select-sm">
        <option value="">All batches</option>
        @foreach($batches as $batch)
            <option value="{{ $batch->id }}" @selected((string) old('batch_id', $entry?->batch_id) === (string) $batch->id)>{{ $batch->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Subject</label>
    <select name="subject_id" class="form-select form-select-sm" required>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $entry?->subject_id) === (string) $subject->id)>{{ $subject->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Teacher</label>
    <select name="teacher_user_id" class="form-select form-select-sm">
        <option value="">Unassigned</option>
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected((string) old('teacher_user_id', $entry?->teacher_user_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
        @endforeach
    </select>
</div>
<div class="row g-2 mb-2">
    <div class="col-6">
        <label class="form-label">Day</label>
        <select name="day_of_week" class="form-select form-select-sm" required>
            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                <option value="{{ $day }}" @selected(old('day_of_week', $entry?->day_of_week) === $day)>{{ ucfirst($day) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-3">
        <label class="form-label">Start</label>
        <input type="time" name="start_time" class="form-control form-control-sm" value="{{ old('start_time', $entry?->start_time) }}" required>
    </div>
    <div class="col-3">
        <label class="form-label">End</label>
        <input type="time" name="end_time" class="form-control form-control-sm" value="{{ old('end_time', $entry?->end_time) }}" required>
    </div>
</div>
<div class="mb-2">
    <label class="form-label">Room</label>
    <input name="room" class="form-control form-control-sm" value="{{ old('room', $entry?->room) }}">
</div>
<div class="mb-2">
    <label class="form-label">Notes</label>
    <input name="notes" class="form-control form-control-sm" value="{{ old('notes', $entry?->notes) }}">
</div>
<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" id="{{ $prefix }}active" name="is_active" value="1" @checked(old('is_active', $entry?->is_active ?? true))>
    <label class="form-check-label" for="{{ $prefix }}active">Active</label>
</div>
