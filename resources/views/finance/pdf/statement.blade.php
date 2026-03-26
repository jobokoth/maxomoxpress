<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement {{ $student->admission_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1, h2, h3, p { margin: 0; }
        .mb-8 { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .grid { width: 100%; margin-bottom: 12px; }
        .chip { display: inline-block; border: 1px solid #ccc; padding: 6px 8px; margin-right: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f3f3; }
        .section-title { margin-top: 14px; margin-bottom: 6px; font-size: 13px; }
    </style>
</head>
<body>
    <h2 class="mb-8">{{ $school->name }}</h2>
    <p class="mb-8">Student Fee Statement</p>
    <p class="mb-8"><strong>Student:</strong> {{ $student->full_name }} ({{ $student->admission_number }})</p>
    <p class="mb-12"><strong>Class:</strong> {{ $student->course?->name }}{{ $student->batch?->name ? ' / '.$student->batch?->name : '' }}</p>

    <div class="grid">
        <span class="chip">Invoiced: {{ number_format($summary['invoiced'], 2) }}</span>
        <span class="chip">Paid: {{ number_format($summary['paid'], 2) }}</span>
        <span class="chip">Balance: {{ number_format($summary['balance'], 2) }}</span>
        <span class="chip">Overdue: {{ number_format($summary['overdue'], 2) }}</span>
    </div>

    <h3 class="section-title">Invoices</h3>
    <table>
        <thead><tr><th>Invoice</th><th>Due</th><th>Final</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->structure?->name }}</td>
                    <td>{{ $assignment->due_date?->format('d M Y') ?: '-' }}</td>
                    <td>{{ number_format((float)$assignment->final_amount, 2) }}</td>
                    <td>{{ number_format((float)$assignment->paid_amount, 2) }}</td>
                    <td>{{ number_format((float)$assignment->balance_amount, 2) }}</td>
                    <td>{{ strtoupper($assignment->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No invoices found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="section-title">Payments</h3>
    <table>
        <thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th>Amount</th></tr></thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ strtoupper($payment->payment_method) }}</td>
                    <td>{{ number_format((float)$payment->amount_paid, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
