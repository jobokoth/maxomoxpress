@php
    $announcement = $announcement ?? null;
    $prefix = $announcement ? 'a_' . $announcement->id . '_' : 'a_new_';
@endphp

<div class="mb-2">
    <label class="form-label">Title</label>
    <input name="title" class="form-control form-control-sm" value="{{ old('title', $announcement?->title) }}" required>
</div>
<div class="mb-2">
    <label class="form-label">Message</label>
    <textarea name="body" class="form-control form-control-sm" rows="3" required>{{ old('body', $announcement?->body) }}</textarea>
</div>
<div class="mb-2">
    <label class="form-label">Audience</label>
    <select name="audience" class="form-select form-select-sm" required>
        @foreach(['all','staff','students','parents','class'] as $audience)
            <option value="{{ $audience }}" @selected(old('audience', $announcement?->audience ?? 'all') === $audience)>{{ ucfirst($audience) }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Course (for class audience)</label>
    <select name="course_id" class="form-select form-select-sm">
        <option value="">None</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" @selected((string) old('course_id', $announcement?->course_id) === (string) $course->id)>{{ $course->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label class="form-label">Batch</label>
    <select name="batch_id" class="form-select form-select-sm">
        <option value="">None</option>
        @foreach($batches as $batch)
            <option value="{{ $batch->id }}" @selected((string) old('batch_id', $announcement?->batch_id) === (string) $batch->id)>{{ $batch->name }}</option>
        @endforeach
    </select>
</div>
<div class="row g-2 mb-2">
    <div class="col-6">
        <label class="form-label">Publish At</label>
        <input type="datetime-local" name="published_at" class="form-control form-control-sm" value="{{ old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-6">
        <label class="form-label">Expires At</label>
        <input type="datetime-local" name="expires_at" class="form-control form-control-sm" value="{{ old('expires_at', $announcement?->expires_at?->format('Y-m-d\TH:i')) }}">
    </div>
</div>
<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" id="{{ $prefix }}pinned" name="is_pinned" value="1" @checked(old('is_pinned', $announcement?->is_pinned ?? false))>
    <label class="form-check-label" for="{{ $prefix }}pinned">Pin announcement</label>
</div>
