@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">Student Fee Statement</h4>
            <p class="mb-0 text-muted">{{ $student->full_name }} ({{ $student->admission_number }})</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('tenant.finance.index', ['school_slug' => $school->slug]) }}">Back</a>
            <a class="btn btn-outline-primary" href="{{ route('tenant.finance.statements.pdf', ['school_slug' => $school->slug, 'student' => $student->id, 'academic_year_id' => $filters['academicYearId'] ?: null, 'term_id' => $filters['termId'] ?: null]) }}">Download PDF</a>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="col-12">
        <div class="card"><div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($years as $year)
                            <option value="{{ $year->id }}" @selected($filters['academicYearId'] === $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Term</label>
                    <select name="term_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" @selected($filters['termId'] === $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" type="submit">Apply Filters</button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Invoiced</div><h5 class="mb-0">{{ number_format($summary['invoiced'], 2) }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Paid</div><h5 class="mb-0">{{ number_format($summary['paid'], 2) }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Balance</div><h5 class="mb-0">{{ number_format($summary['balance'], 2) }}</h5></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Overdue</div><h5 class="mb-0 text-danger">{{ number_format($summary['overdue'], 2) }}</h5></div></div></div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Invoices</h5></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Invoice</th><th>Due</th><th>Final</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->structure?->name }}<br><small class="text-muted">{{ $assignment->academicYear?->name }}{{ $assignment->term?->name ? ' / '.$assignment->term?->name : '' }}</small></td>
                        <td>{{ $assignment->due_date?->format('d M Y') ?: '-' }}</td>
                        <td>{{ number_format((float)$assignment->final_amount, 2) }}</td>
                        <td>{{ number_format((float)$assignment->paid_amount, 2) }}</td>
                        <td>{{ number_format((float)$assignment->balance_amount, 2) }}</td>
                        <td>{{ strtoupper($assignment->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No invoices found.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Payments</h5></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Receipt</th><th>Amount</th></tr></thead><tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td>{{ $payment->receipt_number }}</td>
                        <td>{{ number_format((float)$payment->amount_paid, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No payments found.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
</div>
@endsection
