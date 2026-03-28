<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') | {{ $school->name }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --masomo-primary: #4f46e5;
            --masomo-primary-dark: #3730a3;
        }
        body { background: #f8f9fa; min-height: 100vh; }
        .portal-navbar {
            background: var(--masomo-primary);
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .portal-navbar .navbar-brand { color: #fff; font-weight: 700; letter-spacing: .02em; }
        .portal-navbar .nav-link { color: rgba(255,255,255,.85); }
        .portal-navbar .nav-link:hover,
        .portal-navbar .nav-link.active { color: #fff; }
        .portal-navbar .navbar-toggler { border-color: rgba(255,255,255,.4); }
        .portal-navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255,255,255,.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .stat-card { border: none; border-radius: 12px; }
        .stat-card .card-body { padding: 1.25rem 1.5rem; }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .badge-present  { background: #d1fae5; color: #065f46; }
        .badge-absent   { background: #fee2e2; color: #991b1b; }
        .badge-late     { background: #fef3c7; color: #92400e; }
        .badge-excused  { background: #dbeafe; color: #1e40af; }
        @media (max-width: 575px) {
            .portal-title { font-size: 1.2rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg portal-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            @if ($school->logo)
                <img src="{{ $school->logo }}" alt="{{ $school->name }}" height="32" class="rounded-1">
            @else
                <i class="bi bi-mortarboard-fill"></i>
            @endif
            <span class="d-none d-sm-inline">{{ $school->name }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="portalNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @yield('nav-links')
            </ul>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <span class="nav-link text-white-50 small">{{ auth()->user()->name }}</span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-box-arrow-right me-1"></i>Sign out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Page header --}}
<div style="background: var(--masomo-primary-dark);" class="py-3">
    <div class="container">
        <h1 class="text-white mb-0 fs-5 fw-semibold portal-title">@yield('page-title')</h1>
        @hasSection('breadcrumb')
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider-color: rgba(255,255,255,.5);">
                    @yield('breadcrumb')
                </ol>
            </nav>
        @endif
    </div>
</div>

{{-- Main content --}}
<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-muted small py-4 mt-auto">
    &copy; {{ date('Y') }} {{ $school->name }} &mdash; Powered by <strong>MasomoXpress</strong>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
