@extends('portal.layouts.app')

@section('title', 'Parent Dashboard')
@section('page-title', 'Parent Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item text-white-50">Home</li>
@endsection

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('portal.parent.dashboard', $slug) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
    @foreach ($children as $child)
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person me-1"></i>{{ $child->first_name }}
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('portal.parent.fees', [$slug, $child]) }}">
                        <i class="bi bi-cash me-2"></i>Fees
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('portal.parent.attendance', [$slug, $child]) }}">
                        <i class="bi bi-calendar-check me-2"></i>Attendance
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('portal.parent.results', [$slug, $child]) }}">
                        <i class="bi bi-graph-up me-2"></i>Results
                    </a>
                </li>
            </ul>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.announcements', $slug) }}">
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
<div class="row g-3 mb-4">
    {{-- Fee summary cards --}}
    <div class="col-6 col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ede9fe;color:#6d28d9;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Billed</div>
                    <div class="fw-bold fs-5">
                        {{ $school->currency ?? 'KES' }} {{ number_format($feeSummary->total_billed ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#d1fae5;color:#065f46;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Paid</div>
                    <div class="fw-bold fs-5">
                        {{ $school->currency ?? 'KES' }} {{ number_format($feeSummary->total_paid ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2;color:#991b1b;">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <div class="text-muted small">Outstanding Balance</div>
                    <div class="fw-bold fs-5 {{ ($feeSummary->total_balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $school->currency ?? 'KES' }} {{ number_format($feeSummary->total_balance ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Children --}}
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold bg-white">
                <i class="bi bi-people me-2 text-primary"></i>My Children
            </div>
            <div class="list-group list-group-flush">
                @forelse ($children as $child)
                    @php $slug = request()->route('school_slug'); @endphp
                    <div class="list-group-item">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:40px;height:40px;font-weight:600;">
                                {{ strtoupper(substr($child->first_name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-truncate">{{ $child->full_name }}</div>
                                <div class="text-muted small">
                                    {{ $child->batch?->name ?? '—' }} &middot; {{ $child->course?->name ?? '—' }}
                                </div>
                                <div class="text-muted small">Adm: {{ $child->admission_number }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <a href="{{ route('portal.parent.fees', [$slug, $child]) }}"
                               class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-cash me-1"></i>Fees
                            </a>
                            <a href="{{ route('portal.parent.attendance', [$slug, $child]) }}"
                               class="btn btn-sm btn-outline-secondary flex-fill">
                                <i class="bi bi-calendar-check me-1"></i>Attendance
                            </a>
                            <a href="{{ route('portal.parent.results', [$slug, $child]) }}"
                               class="btn btn-sm btn-outline-secondary flex-fill">
                                <i class="bi bi-graph-up me-1"></i>Results
                            </a>
                        </div>
                        {{-- Student portal access toggle --}}
                        <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                            <span class="small text-muted">Student portal:</span>
                            @if ($child->portal_access_granted)
                                <form method="POST" action="{{ route('portal.parent.student.revoke-access', [$slug, $child]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Revoke portal access for {{ $child->first_name }}?')">
                                        <i class="bi bi-lock me-1"></i>Revoke Access
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('portal.parent.student.grant-access', [$slug, $child]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-unlock me-1"></i>Grant Access
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted text-center py-4">
                        <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                        No children linked to your account.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="row g-4">
            {{-- Recent Payments --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold bg-white">
                        <i class="bi bi-receipt me-2 text-success"></i>Recent Payments
                    </div>
                    <div class="card-body p-0">
                        @if ($recentPayments->isEmpty())
                            <p class="text-muted text-center py-4 mb-0">No payments recorded yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Ref</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentPayments as $payment)
                                            <tr>
                                                <td>{{ $payment->student->full_name }}</td>
                                                <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                                <td class="fw-semibold text-success">
                                                    {{ number_format($payment->amount_paid, 2) }}
                                                </td>
                                                <td>{{ ucfirst($payment->payment_method ?? '—') }}</td>
                                                <td class="text-muted small">{{ $payment->transaction_reference ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-semibold"><i class="bi bi-megaphone me-2 text-warning"></i>Announcements</span>
                        <a href="{{ route('portal.parent.announcements', request()->route('school_slug')) }}"
                           class="btn btn-sm btn-link p-0">View all</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($announcements as $ann)
                            <div class="list-group-item">
                                <div class="fw-semibold small text-truncate">{{ $ann->title }}</div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ $ann->published_at?->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted text-center small py-3">No announcements.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Events --}}
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-semibold"><i class="bi bi-calendar-event me-2 text-info"></i>Upcoming Events</span>
                        <a href="{{ route('portal.parent.events', request()->route('school_slug')) }}"
                           class="btn btn-sm btn-link p-0">View all</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($events as $event)
                            <div class="list-group-item">
                                <div class="fw-semibold small text-truncate">{{ $event->title }}</div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ $event->start_at?->format('d M Y') }}
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted text-center small py-3">No upcoming events.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
