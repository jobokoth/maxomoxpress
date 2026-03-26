@extends('portal.layouts.app')

@section('title', 'Events')
@section('page-title', 'School Events')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('portal.parent.dashboard', request()->route('school_slug')) }}" class="text-white-50">Dashboard</a>
    </li>
    <li class="breadcrumb-item text-white">Events</li>
@endsection

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.dashboard', $slug) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.announcements', $slug) }}">
            <i class="bi bi-megaphone me-1"></i>Announcements
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('portal.parent.events', $slug) }}">
            <i class="bi bi-calendar-event me-1"></i>Events
        </a>
    </li>
@endsection

@section('content')
@if ($events->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-calendar-x display-4 d-block mb-2 opacity-25"></i>
            No events scheduled.
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($events as $event)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div class="text-center flex-shrink-0" style="min-width:48px;">
                                <div class="fw-bold fs-4 text-primary lh-1">
                                    {{ $event->start_at?->format('d') }}
                                </div>
                                <div class="text-muted small text-uppercase">
                                    {{ $event->start_at?->format('M') }}
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">{{ $event->title }}</h6>
                                @if ($event->description)
                                    <p class="text-muted small mb-1">{{ Str::limit($event->description, 80) }}</p>
                                @endif
                                @if ($event->location)
                                    <div class="text-muted small">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $event->location }}
                                    </div>
                                @endif
                                @if ($event->start_at && $event->end_at)
                                    <div class="text-muted small">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $event->start_at->format('g:i A') }} – {{ $event->end_at->format('g:i A') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $events->links() }}</div>
@endif
@endsection
