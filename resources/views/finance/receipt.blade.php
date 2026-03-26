@extends('layouts.dashui')

@section('content')
<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Fee Receipt</h4>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('tenant.finance.receipts.pdf', ['school_slug' => $school->slug, 'payment' => $payment->id]) }}">Download PDF</a>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">Print</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <h5 class="mb-1">{{ $school->name }}</h5>
                        <p class="mb-0 text-muted">Receipt No: {{ $payment->receipt_number }}</p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-0"><strong>Date:</strong> {{ $payment->payment_date?->format('d M Y') }}</p>
                        <p class="mb-0"><strong>Method:</strong> {{ strtoupper($payment->payment_method) }}</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <p class="mb-1"><strong>Student:</strong> {{ $payment->student?->full_name }}</p>
                    <p class="mb-1"><strong>Admission:</strong> {{ $payment->student?->admission_number }}</p>
                    <p class="mb-1"><strong>Class:</strong> {{ $payment->student?->course?->name }}{{ $payment->student?->batch?->name ? ' / '.$payment->student?->batch?->name : '' }}</p>
                    <p class="mb-0"><strong>Invoice:</strong> {{ $payment->assignment?->structure?->name }}</p>
                </div>

                <table class="table table-bordered">
                    <tr><th width="40%">Amount Paid</th><td>{{ number_format((float)$payment->amount_paid, 2) }}</td></tr>
                    <tr><th>Transaction Reference</th><td>{{ $payment->transaction_reference ?: '-' }}</td></tr>
                    <tr><th>Collected By</th><td>{{ $payment->collectedBy?->name ?: '-' }}</td></tr>
                    <tr><th>Notes</th><td>{{ $payment->notes ?: '-' }}</td></tr>
                </table>

                <div class="text-end mt-4">
                    <a href="{{ route('tenant.finance.index', ['school_slug' => $school->slug]) }}" class="btn btn-outline-secondary">Back to Finance</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
