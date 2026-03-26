<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1, h2, h3, p { margin: 0; }
        .header { margin-bottom: 14px; }
        .muted { color: #666; }
        .row { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; width: 38%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $school->name }}</h2>
        <p class="muted">Fee Payment Receipt</p>
    </div>

    <div class="row"><strong>Receipt Number:</strong> {{ $payment->receipt_number }}</div>
    <div class="row"><strong>Date:</strong> {{ $payment->payment_date?->format('d M Y') }}</div>
    <div class="row"><strong>Method:</strong> {{ strtoupper($payment->payment_method) }}</div>

    <table>
        <tr><th>Student</th><td>{{ $payment->student?->full_name }}</td></tr>
        <tr><th>Admission Number</th><td>{{ $payment->student?->admission_number }}</td></tr>
        <tr><th>Class</th><td>{{ $payment->student?->course?->name }}{{ $payment->student?->batch?->name ? ' / '.$payment->student?->batch?->name : '' }}</td></tr>
        <tr><th>Invoice</th><td>{{ $payment->assignment?->structure?->name }}</td></tr>
        <tr><th>Amount Paid</th><td>{{ number_format((float)$payment->amount_paid, 2) }}</td></tr>
        <tr><th>Transaction Reference</th><td>{{ $payment->transaction_reference ?: '-' }}</td></tr>
        <tr><th>Collected By</th><td>{{ $payment->collectedBy?->name ?: '-' }}</td></tr>
        <tr><th>Notes</th><td>{{ $payment->notes ?: '-' }}</td></tr>
    </table>
</body>
</html>
