@extends('portal.layouts.app')

@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('portal.parent.dashboard', request()->route('school_slug')) }}" class="text-white-50">Dashboard</a>
    </li>
    <li class="breadcrumb-item text-white">Announcements</li>
@endsection

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.dashboard', $slug) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('portal.parent.announcements', $slug) }}">
            <i class="bi bi-megaphone me-1"></i>Announcements
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.events', $slug) }}">
            <i class="bi bi-calendar-event me-1"></i>Events
        </a>
    </li>
@endsection

@section('content')
@if ($announcements->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-muted text-center py-5">
            <i class="bi bi-megaphone display-4 d-block mb-2 opacity-25"></i>
            No announcements yet.
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($announcements as $ann)
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h6 class="fw-bold mb-1">{{ $ann->title }}</h6>
                            <span class="text-muted small text-nowrap">
                                {{ $ann->published_at?->format('d M Y') ?? 'Draft' }}
                            </span>
                        </div>
                        <p class="text-muted mb-0 small">{{ $ann->body }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $announcements->links() }}</div>
@endif
@endsection
