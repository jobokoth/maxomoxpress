@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12">
        <h4 class="mb-1">Parent Portal</h4>
        <p class="text-muted mb-0">Guardian: {{ $guardian?->full_name ?? auth()->user()->name }}</p>
    </div>

    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Children</div><h5 class="mb-0">{{ $children->count() }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Fee Balance</div><h5 class="mb-0">{{ number_format((float)$fees->sum('balance_amount'), 2) }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Upcoming Events</div><h5 class="mb-0">{{ $events->count() }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Recent Results</div><h5 class="mb-0">{{ $marks->count() }}</h5></div></div></div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">My Children</h6></div>
            <div class="card-body">
                @forelse($children as $child)
                    <div class="border rounded p-2 mb-2">
                        <strong>{{ $child->full_name }}</strong><br>
                        <small class="text-muted">{{ $child->course?->name }}{{ $child->batch?->name ? ' / '.$child->batch?->name : '' }}</small><br>
                        <small class="text-muted">Adm: {{ $child->admission_number }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No linked students.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Announcements</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Title</th><th>Published</th></tr></thead><tbody>@forelse($announcements as $announcement)<tr><td>{{ $announcement->title }}</td><td>{{ $announcement->published_at?->format('d M Y H:i') ?: '-' }}</td></tr>@empty<tr><td colspan="2" class="text-muted text-center py-3">No announcements.</td></tr>@endforelse</tbody></table></div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Upcoming Events</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Event</th><th>Date/Time</th><th>Location</th></tr></thead><tbody>@forelse($events as $event)<tr><td>{{ $event->title }}</td><td>{{ $event->start_at?->format('d M Y H:i') }}</td><td>{{ $event->location ?: '-' }}</td></tr>@empty<tr><td colspan="3" class="text-muted text-center py-3">No upcoming events.</td></tr>@endforelse</tbody></table></div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Fee Summary</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Invoice</th><th>Final</th><th>Balance</th><th>Status</th></tr></thead><tbody>@forelse($fees as $fee)<tr><td>{{ $fee->student?->full_name }}</td><td>{{ $fee->structure?->name }}</td><td>{{ number_format((float)$fee->final_amount, 2) }}</td><td>{{ number_format((float)$fee->balance_amount, 2) }}</td><td>{{ strtoupper($fee->status) }}</td></tr>@empty<tr><td colspan="5" class="text-muted text-center py-3">No fee data.</td></tr>@endforelse</tbody></table></div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Recent Results</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead><tbody>@forelse($marks as $mark)<tr><td>{{ $mark->student?->full_name }}</td><td>{{ $mark->exam?->name }}</td><td>{{ $mark->subject?->name }}</td><td>{{ number_format((float)$mark->marks_obtained, 2) }}</td><td>{{ $mark->grade_letter }}</td></tr>@empty<tr><td colspan="5" class="text-muted text-center py-3">No result records.</td></tr>@endforelse</tbody></table></div>
        </div>
    </div>
</div>
@endsection
