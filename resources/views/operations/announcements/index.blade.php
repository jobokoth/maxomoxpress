@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-4">
        @can('announcements.manage')
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">New Announcement</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tenant.operations.announcements.store', ['school_slug' => $school->slug]) }}">
                        @csrf
                        @include('operations.announcements.partials.form', ['announcement' => null])
                        <button class="btn btn-primary w-100" type="submit">Publish</button>
                    </form>
                </div>
            </div>
        @endcan
    </div>

    <div class="col-lg-8">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="card">
            <div class="card-header"><h4 class="mb-0">Announcements</h4></div>
            <div class="card-body">
                @forelse($announcements as $announcement)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1">{{ $announcement->title }} @if($announcement->is_pinned)<span class="badge bg-warning text-dark">Pinned</span>@endif</h5>
                                <small class="text-muted">Audience: {{ ucfirst($announcement->audience) }} | Posted: {{ $announcement->published_at?->format('d M Y H:i') }}</small>
                            </div>
                            <small class="text-muted">Reads: {{ $announcement->reads_count }}</small>
                        </div>
                        <p class="mb-2">{{ $announcement->body }}</p>
                        <small class="text-muted d-block mb-2">Class: {{ $announcement->course?->name ?: 'N/A' }} / {{ $announcement->batch?->name ?: 'N/A' }}</small>

                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('tenant.operations.announcements.read', ['school_slug' => $school->slug, 'announcement' => $announcement->id]) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Mark Read</button>
                            </form>

                            @can('announcements.manage')
                                <details>
                                    <summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                    <div class="mt-2">
                                        <form method="POST" action="{{ route('tenant.operations.announcements.update', ['school_slug' => $school->slug, 'announcement' => $announcement->id]) }}" class="mb-2">
                                            @csrf @method('PUT')
                                            @include('operations.announcements.partials.form', ['announcement' => $announcement])
                                            <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                        </form>
                                        <form method="POST" action="{{ route('tenant.operations.announcements.destroy', ['school_slug' => $school->slug, 'announcement' => $announcement->id]) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </details>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No announcements yet.</p>
                @endforelse
            </div>
            <div class="card-footer">{{ $announcements->links() }}</div>
        </div>
    </div>
</div>
@endsection
