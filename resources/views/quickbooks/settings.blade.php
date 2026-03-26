<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuickBooks Integration | {{ $school->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:900px;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white"
             style="width:48px;height:48px;font-size:1.4rem;background:#2CA01C;">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold">QuickBooks Integration</h4>
            <div class="text-muted small">{{ $school->name }} &mdash; Sync fee payments to QuickBooks Online</div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ═══════════════════════════ CONNECTION STATUS ═══════════════════════════ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold {{ $connection ? 'bg-success text-white' : 'bg-secondary text-white' }}">
            <i class="bi bi-plug me-2"></i>Connection Status
        </div>
        <div class="card-body">
            @if ($connection)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Connected</span>
                    <span class="text-muted small">
                        Company ID: <code>{{ $connection->realm_id }}</code>
                        &bull; Environment: <span class="badge bg-{{ $connection->environment === 'production' ? 'primary' : 'warning text-dark' }}">{{ $connection->environment }}</span>
                        &bull; Connected {{ $connection->connected_at->diffForHumans() }}
                    </span>
                </div>

                @if ($connection->company_name)
                    <p class="mb-2"><strong>Company:</strong> {{ $connection->company_name }}</p>
                @endif

                <p class="mb-3 text-muted small">
                    Token expires {{ $connection->token_expires_at->diffForHumans() }}.
                    Tokens are refreshed automatically before expiry.
                </p>

                <form method="POST" action="{{ route('tenant.quickbooks.disconnect', request()->route('school_slug')) }}"
                      onsubmit="return confirm('Disconnect QuickBooks? Synced data in QB will not be deleted.')">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-plug-fill me-1"></i>Disconnect QuickBooks
                    </button>
                </form>
            @else
                <p class="mb-3">
                    Connect your QuickBooks Online company to automatically sync fee payments as
                    <strong>Sales Receipts</strong> and students as <strong>Customers</strong>.
                </p>
                <a href="{{ route('tenant.quickbooks.connect', request()->route('school_slug')) }}"
                   class="btn btn-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Connect QuickBooks Online
                </a>
            @endif
        </div>
    </div>

    @if ($connection)
    {{-- ═══════════════════════════ MANUAL SYNC ═══════════════════════════════ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-arrow-repeat me-2"></i>Manual Sync
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                New records sync automatically after each payment or student registration.
                Use bulk sync to backfill historical records.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('tenant.quickbooks.sync-all', request()->route('school_slug')) }}">
                    @csrf
                    <input type="hidden" name="mode" value="students">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-people me-1"></i>Sync All Students → QB Customers
                    </button>
                </form>
                <form method="POST" action="{{ route('tenant.quickbooks.sync-all', request()->route('school_slug')) }}">
                    @csrf
                    <input type="hidden" name="mode" value="payments">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-cash-coin me-1"></i>Sync Unsynced Payments → QB
                    </button>
                </form>
                <form method="POST" action="{{ route('tenant.quickbooks.sync-all', request()->route('school_slug')) }}">
                    @csrf
                    <input type="hidden" name="mode" value="all">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>Full Bulk Sync
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ SYNC STATS ════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-primary">{{ number_format($syncStats['total']) }}</div>
                    <div class="text-muted small">Total Sync Operations</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ number_format($syncStats['success']) }}</div>
                    <div class="text-muted small">Successful</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-danger">{{ number_format($syncStats['failed']) }}</div>
                    <div class="text-muted small">Failed</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ SYNC LOG ══════════════════════════════════ --}}
    <div class="card shadow-sm">
        <div class="card-header fw-semibold">
            <i class="bi bi-clock-history me-2"></i>Recent Sync Activity
        </div>
        <div class="card-body p-0">
            @if ($recentLogs->isEmpty())
                <p class="text-muted p-3 mb-0">No sync activity yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entity</th>
                                <th>QB Type</th>
                                <th>QB ID</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentLogs as $log)
                            <tr>
                                <td class="small">{{ ucfirst(str_replace('_', ' ', $log->entity_type)) }} #{{ $log->entity_id }}</td>
                                <td class="small">{{ $log->qb_entity_type }}</td>
                                <td class="small"><code>{{ $log->qb_id ?? '—' }}</code></td>
                                <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                                <td>
                                    @if ($log->status === 'success')
                                        <span class="badge bg-success">success</span>
                                    @elseif ($log->status === 'failed')
                                        <span class="badge bg-danger"
                                              title="{{ $log->error_message }}"
                                              data-bs-toggle="tooltip">failed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->status }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->synced_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
</script>
</body>
</html>
