@extends('portal.layouts.app')

@section('title', 'Fee Statement — ' . $student->full_name)
@section('page-title', 'Fee Statement')

@section('breadcrumb')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="breadcrumb-item">
        <a href="{{ route('portal.parent.dashboard', $slug) }}" class="text-white-50">Dashboard</a>
    </li>
    <li class="breadcrumb-item text-white">{{ $student->full_name }}</li>
@endsection

@section('nav-links')
    @php $slug = request()->route('school_slug'); @endphp
    <li class="nav-item">
        <a class="nav-link" href="{{ route('portal.parent.dashboard', $slug) }}">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </li>
@endsection

@section('content')
{{-- Student summary --}}
<div class="card shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:56px;height:56px;font-size:1.5rem;font-weight:700;">
            {{ strtoupper(substr($student->first_name, 0, 1)) }}
        </div>
        <div>
            <h5 class="mb-0 fw-bold">{{ $student->full_name }}</h5>
            <div class="text-muted small">
                {{ $student->batch?->name ?? '—' }} &middot; {{ $student->course?->name ?? '—' }}
                &middot; Adm No: {{ $student->admission_number }}
            </div>
        </div>
    </div>
</div>

{{-- Fee totals --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted small">Total Billed</div>
                <div class="fw-bold fs-5">{{ number_format($totalBilled, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted small">Total Paid</div>
                <div class="fw-bold fs-5 text-success">{{ number_format($totalPaid, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card stat-card shadow-sm text-center">
            <div class="card-body">
                <div class="text-muted small">Balance</div>
                <div class="fw-bold fs-5 {{ $totalBalance > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($totalBalance, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fee assignments table --}}
<div class="card shadow-sm">
    <div class="card-header fw-semibold bg-white">Fee Records</div>
    @if ($feeAssignments->isEmpty())
        <div class="card-body text-muted text-center py-5">No fee records found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Term / Year</th>
                        <th>Fee Type</th>
                        <th>Billed</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feeAssignments as $assignment)
                        <tr>
                            <td class="small">
                                {{ $assignment->term?->name ?? '—' }}<br>
                                <span class="text-muted">{{ $assignment->academicYear?->name ?? '—' }}</span>
                            </td>
                            <td>{{ $assignment->structure?->name ?? '—' }}</td>
                            <td>{{ number_format($assignment->final_amount, 2) }}</td>
                            <td class="text-success">{{ number_format($assignment->paid_amount, 2) }}</td>
                            <td class="{{ $assignment->balance_amount > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                {{ number_format($assignment->balance_amount, 2) }}
                            </td>
                            <td class="small">{{ $assignment->due_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = match($assignment->status) {
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'overdue' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($assignment->status ?? 'pending') }}</span>
                            </td>
                        </tr>
                        {{-- Payment sub-rows --}}
                        @foreach ($assignment->payments as $payment)
                            <tr class="table-light">
                                <td colspan="2" class="ps-4 text-muted small">
                                    <i class="bi bi-arrow-return-right me-1"></i>
                                    Payment on {{ $payment->payment_date?->format('d M Y') }}
                                    &mdash; {{ $payment->payment_method ?? '—' }}
                                    @if ($payment->transaction_reference)
                                        &mdash; Ref: {{ $payment->transaction_reference }}
                                    @endif
                                </td>
                                <td></td>
                                <td class="text-success small fw-semibold">
                                    +{{ number_format($payment->amount_paid, 2) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
