@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    </div>

    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">Total Invoiced</div><h4 class="mb-0">{{ number_format($totals['invoiced'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">Total Collected</div><h4 class="mb-0">{{ number_format($totals['collected'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">Outstanding Balance</div><h4 class="mb-0">{{ number_format($totals['balance'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><div class="text-muted small">Arrears</div><h4 class="mb-0 text-danger">{{ number_format($totals['arrears'], 2) }}</h4></div></div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Fee Category</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.finance.categories.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><input name="name" class="form-control form-control-sm" placeholder="Category name" required></div>
                    <div class="mb-2"><input name="description" class="form-control form-control-sm" placeholder="Description"></div>
                    <div class="form-check mb-2"><input class="form-check-input" id="is_mandatory" type="checkbox" name="is_mandatory" value="1" checked><label class="form-check-label" for="is_mandatory">Mandatory</label></div>
                    <button class="btn btn-primary btn-sm w-100" type="submit">Add Category</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Fee Structure</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.finance.structures.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select form-select-sm" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Term</label><select name="term_id" class="form-select form-select-sm"><option value="">All terms</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Course</label><select name="course_id" class="form-select form-select-sm" required>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><option value="">All batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->course?->name }} / {{ $batch->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Category</label><select name="fee_category_id" class="form-select form-select-sm" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><input name="name" class="form-control form-control-sm" placeholder="e.g. Term 1 Tuition" required></div>
                    <div class="mb-2"><input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm" placeholder="Amount" required></div>
                    <div class="mb-2"><input type="date" name="due_date" class="form-control form-control-sm"></div>
                    <div class="mb-2"><select name="frequency" class="form-select form-select-sm" required><option value="once">Once</option><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="annually">Annually</option></select></div>
                    <div class="form-check mb-2"><input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" checked><label class="form-check-label" for="is_active">Active</label></div>
                    <button class="btn btn-primary btn-sm w-100" type="submit">Create Structure</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Generate Invoices</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.finance.invoices.generate', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><label class="form-label">Academic Year</label><select name="academic_year_id" class="form-select form-select-sm" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Term</label><select name="term_id" class="form-select form-select-sm"><option value="">Any term</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Course</label><select name="course_id" class="form-select form-select-sm" required>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Batch</label><select name="batch_id" class="form-select form-select-sm"><option value="">All batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->course?->name }} / {{ $batch->name }}</option>@endforeach</select></div>
                    <div class="mb-2"><label class="form-label">Due Date Override</label><input type="date" name="due_date" class="form-control form-control-sm"></div>
                    <div class="row g-2 mb-2"><div class="col"><input type="number" min="0" step="0.01" name="scholarship_amount" class="form-control form-control-sm" placeholder="Scholarship"></div><div class="col"><input type="number" min="0" step="0.01" name="discount_amount" class="form-control form-control-sm" placeholder="Discount"></div></div>
                    <div class="row g-2 mb-2"><div class="col"><input type="number" min="0" step="0.01" name="fine_amount" class="form-control form-control-sm" placeholder="Fine"></div><div class="col"><input name="adjustment_reason" class="form-control form-control-sm" placeholder="Reason"></div></div>
                    <div class="mb-2"><input name="notes" class="form-control form-control-sm" placeholder="Optional notes"></div>
                    <button class="btn btn-warning btn-sm w-100" type="submit">Generate Invoices</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Record Payment</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.finance.payments.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="mb-2"><label class="form-label">Invoice</label><select name="fee_assignment_id" class="form-select form-select-sm" required>@foreach($assignments as $assignment)<option value="{{ $assignment->id }}">{{ $assignment->student?->full_name }} | {{ $assignment->structure?->name }} | Bal {{ number_format((float)$assignment->balance_amount, 2) }}</option>@endforeach</select></div>
                    <div class="mb-2"><input type="number" min="0.01" step="0.01" name="amount_paid" class="form-control form-control-sm" placeholder="Amount paid" required></div>
                    <div class="mb-2"><input type="date" name="payment_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div>
                    <div class="mb-2"><select name="payment_method" class="form-select form-select-sm" required><option value="cash">Cash</option><option value="bank">Bank</option><option value="card">Card</option><option value="online">Online</option><option value="cheque">Cheque</option></select></div>
                    <div class="mb-2"><input name="transaction_reference" class="form-control form-control-sm" placeholder="Transaction ref"></div>
                    <div class="mb-2"><input name="notes" class="form-control form-control-sm" placeholder="Notes"></div>
                    <button class="btn btn-success btn-sm w-100" type="submit">Save Payment + Receipt</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Fee Structures</h5></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Class/Term</th><th>Category</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead><tbody>
                @forelse($structures as $structure)
                    <tr>
                        <td>{{ $structure->name }}</td>
                        <td>{{ $structure->course?->name }}{{ $structure->batch?->name ? ' / '.$structure->batch?->name : '' }}<br><small class="text-muted">{{ $structure->academicYear?->name }}{{ $structure->term?->name ? ' / '.$structure->term?->name : '' }}</small></td>
                        <td>{{ $structure->category?->name }}</td>
                        <td>{{ number_format((float)$structure->amount, 2) }}</td>
                        <td>{{ $structure->due_date?->format('d M Y') ?: '-' }}</td>
                        <td>{!! $structure->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No fee structures available.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $structures->appends(request()->query())->links() }}</div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Invoices, Arrears & Adjustments</h5>
            </div>
            <div class="card-body border-bottom">
                <form method="GET" class="row g-2">
                    <div class="col-md-3"><select name="course_id" class="form-select form-select-sm"><option value="">All courses</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($filters['courseId'] === $course->id)>{{ $course->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><select name="batch_id" class="form-select form-select-sm"><option value="">All batches</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" @selected($filters['batchId'] === $batch->id)>{{ $batch->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><select name="student_id" class="form-select form-select-sm"><option value="">All students</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected($filters['studentId'] === $student->id)>{{ $student->full_name }}</option>@endforeach</select></div>
                    <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>@foreach(['pending','partial','paid','waived','overdue'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ strtoupper($status) }}</option>@endforeach</select></div>
                    <div class="col-md-1 d-grid"><button class="btn btn-outline-secondary btn-sm" type="submit">Go</button></div>
                </form>
            </div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Invoice</th><th>Due</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th>Adjust</th></tr></thead><tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->student?->full_name }}<br><small class="text-muted">{{ $assignment->student?->admission_number }}</small></td>
                        <td>{{ $assignment->structure?->name }}<br><small class="text-muted">{{ $assignment->structure?->category?->name }}</small></td>
                        <td>{{ $assignment->due_date?->format('d M Y') ?: '-' }}</td>
                        <td>{{ number_format((float)$assignment->final_amount, 2) }}</td>
                        <td>{{ number_format((float)$assignment->paid_amount, 2) }}</td>
                        <td>{{ number_format((float)$assignment->balance_amount, 2) }}</td>
                        <td>
                            @if($assignment->status === 'overdue')
                                <span class="badge bg-danger">OVERDUE</span>
                            @elseif($assignment->status === 'paid')
                                <span class="badge bg-success">PAID</span>
                            @elseif($assignment->status === 'partial')
                                <span class="badge bg-warning text-dark">PARTIAL</span>
                            @elseif($assignment->status === 'waived')
                                <span class="badge bg-info text-dark">WAIVED</span>
                            @else
                                <span class="badge bg-secondary">PENDING</span>
                            @endif
                        </td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                <form class="mt-2" method="POST" action="{{ route('tenant.finance.assignments.adjustments.update', ['school_slug' => $school->slug, 'assignment' => $assignment->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-1 mb-1"><div class="col"><input name="scholarship_amount" type="number" min="0" step="0.01" class="form-control form-control-sm" value="{{ $assignment->scholarship_amount }}" placeholder="Scholarship"></div><div class="col"><input name="discount_amount" type="number" min="0" step="0.01" class="form-control form-control-sm" value="{{ $assignment->discount_amount }}" placeholder="Discount"></div></div>
                                    <div class="row g-1 mb-1"><div class="col"><input name="fine_amount" type="number" min="0" step="0.01" class="form-control form-control-sm" value="{{ $assignment->fine_amount }}" placeholder="Fine"></div><div class="col"><input name="due_date" type="date" class="form-control form-control-sm" value="{{ $assignment->due_date?->format('Y-m-d') }}"></div></div>
                                    <input name="adjustment_reason" class="form-control form-control-sm mb-1" value="{{ $assignment->adjustment_reason }}" placeholder="Reason">
                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                </form>
                                <a class="btn btn-sm btn-outline-secondary mt-2" href="{{ route('tenant.finance.statements.show', ['school_slug' => $school->slug, 'student' => $assignment->student_id]) }}">Statement</a>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No invoices found.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $assignments->appends(request()->query())->links() }}</div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Payments & Receipts</h5></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Student</th><th>Receipt</th><th>Method</th><th>Amount</th><th>Action</th></tr></thead><tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td>{{ $payment->student?->full_name }}</td>
                        <td>{{ $payment->receipt_number }}</td>
                        <td>{{ strtoupper($payment->payment_method) }}</td>
                        <td>{{ number_format((float)$payment->amount_paid, 2) }}</td>
                        <td class="d-flex gap-1">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('tenant.finance.receipts.show', ['school_slug' => $school->slug, 'payment' => $payment->id]) }}">Receipt</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.finance.receipts.pdf', ['school_slug' => $school->slug, 'payment' => $payment->id]) }}">PDF</a>
                            <a class="btn btn-sm btn-outline-dark" href="{{ route('tenant.finance.statements.show', ['school_slug' => $school->slug, 'student' => $payment->student_id]) }}">Statement</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No payments recorded.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $payments->appends(request()->query())->links() }}</div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Arrears List</h5></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Invoice</th><th>Due Date</th><th>Balance</th></tr></thead><tbody>
                @forelse($arrearsList as $arrear)
                    <tr>
                        <td>{{ $arrear->student?->full_name }}</td>
                        <td>{{ $arrear->structure?->name }}</td>
                        <td>{{ $arrear->due_date?->format('d M Y') ?: '-' }}</td>
                        <td class="text-danger">{{ number_format((float)$arrear->balance_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No arrears currently.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
</div>
@endsection
