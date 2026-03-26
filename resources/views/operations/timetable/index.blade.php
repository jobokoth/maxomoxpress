@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Add Timetable Entry</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.operations.timetable.store', ['school_slug' => $school->slug]) }}">
                    @csrf
                    @include('operations.timetable.partials.form', ['entry' => null])
                    <button class="btn btn-primary w-100" type="submit">Create Entry</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Timetable Schedule</h4></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Day/Time</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst($entry->day_of_week) }}</div>
                                    <small class="text-muted">{{ substr($entry->start_time, 0, 5) }} - {{ substr($entry->end_time, 0, 5) }}</small>
                                </td>
                                <td>
                                    <div>{{ $entry->course?->name }}</div>
                                    <small class="text-muted">{{ $entry->batch?->name ?: 'All batches' }}</small>
                                </td>
                                <td>
                                    <div>{{ $entry->subject?->name }}</div>
                                    <small class="text-muted">{{ $entry->room ?: 'No room' }}</small>
                                </td>
                                <td>{{ $entry->teacher?->name ?: 'Unassigned' }}</td>
                                <td>
                                    <details>
                                        <summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                        <div class="mt-2">
                                            <form method="POST" action="{{ route('tenant.operations.timetable.update', ['school_slug' => $school->slug, 'entry' => $entry->id]) }}" class="mb-2">
                                                @csrf @method('PUT')
                                                @include('operations.timetable.partials.form', ['entry' => $entry])
                                                <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('tenant.operations.timetable.destroy', ['school_slug' => $school->slug, 'entry' => $entry->id]) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No timetable entries.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $entries->links() }}</div>
        </div>
    </div>
</div>
@endsection
