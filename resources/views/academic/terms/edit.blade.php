@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="mb-0">Edit Term</h4></div><div class="card-body">
    <form method="POST" action="{{ route('tenant.terms.update', ['school_slug' => $school->slug, 'term' => $term->id]) }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select" required>@foreach($years as $year)<option value="{{ $year->id }}" @selected((string) old('academic_year_id', $term->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $term->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $term->start_date?->format('Y-m-d')) }}" required></div>
        <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', $term->end_date?->format('Y-m-d')) }}" required></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" @checked(old('is_current', $term->is_current))><label class="form-check-label" for="is_current">Set as current</label></div>
        <button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-outline-secondary" href="{{ route('tenant.terms.index', ['school_slug' => $school->slug]) }}">Cancel</a>
    </form>
</div></div></div></div>
@endsection
