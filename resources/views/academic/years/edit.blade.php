@extends('layouts.dashui')

@section('content')
<div class="row mt-4"><div class="col-lg-8">
    <div class="card">
        <div class="card-header"><h4 class="mb-0">Edit Academic Year</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenant.academic-years.update', ['school_slug' => $school->slug, 'academicYear' => $academicYear->id]) }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $academicYear->name) }}" required></div>
                <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}" required></div>
                <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}" required></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" @checked(old('is_current', $academicYear->is_current))><label class="form-check-label" for="is_current">Set as current</label></div>
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-outline-secondary" href="{{ route('tenant.academic-years.index', ['school_slug' => $school->slug]) }}">Cancel</a>
            </form>
        </div>
    </div>
</div></div>
@endsection
