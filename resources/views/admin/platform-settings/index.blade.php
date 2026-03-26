<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Platform Settings | Masomo Admin</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('dashui/assets/images/favicon/favicon.ico') }}" />
    <script src="{{ asset('dashui/assets/js/vendors/color-modes.js') }}"></script>
    <link href="{{ asset('dashui/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('dashui/assets/css/theme.min.css') }}">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">Platform Settings</h2>
            <p class="text-muted mb-0">Super admin — global configuration</p>
        </div>
        <a href="{{ route('admin.platform-settings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Setting
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                        <tr>
                            <td><code>{{ $setting->key }}</code></td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width:200px;">
                                    {{ $setting->value ?? '<em class="text-muted">null</em>' }}
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $setting->type }}</span></td>
                            <td class="text-muted small">{{ $setting->description }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.platform-settings.edit', $setting) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.platform-settings.destroy', $setting) }}" class="d-inline"
                                      onsubmit="return confirm('Delete setting \'{{ $setting->key }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No platform settings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($settings->hasPages())
            <div class="card-footer">{{ $settings->links() }}</div>
        @endif
    </div>
</div>
<script src="{{ asset('dashui/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
