<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>{{ $isEdit ? 'Edit' : 'Create' }} Setting | Masomo Admin</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('dashui/assets/images/favicon/favicon.ico') }}" />
    <script src="{{ asset('dashui/assets/js/vendors/color-modes.js') }}"></script>
    <link href="{{ asset('dashui/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('dashui/assets/css/theme.min.css') }}">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.platform-settings.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0">{{ $isEdit ? 'Edit Setting: ' . $setting->key : 'New Platform Setting' }}</h4>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST"
                          action="{{ $isEdit ? route('admin.platform-settings.update', $setting) : route('admin.platform-settings.store') }}">
                        @csrf
                        @if ($isEdit) @method('PUT') @endif

                        @if (!$isEdit)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Key <span class="text-danger">*</span></label>
                                <input type="text" name="key" class="form-control @error('key') is-invalid @enderror"
                                       value="{{ old('key', $setting->key) }}"
                                       placeholder="e.g. trial_days" required>
                                <div class="form-text">Unique identifier in snake_case.</div>
                                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Key</label>
                                <input type="text" class="form-control bg-light" value="{{ $setting->key }}" disabled>
                                <div class="form-text">Key cannot be changed after creation.</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach (['string', 'integer', 'boolean', 'json'] as $t)
                                    <option value="{{ $t }}" {{ old('type', $setting->type ?? 'string') === $t ? 'selected' : '' }}>
                                        {{ ucfirst($t) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Value</label>
                            <textarea name="value" class="form-control @error('value') is-invalid @enderror" rows="3">{{ old('value', $setting->value) }}</textarea>
                            <div class="form-text">For boolean: use <code>true</code> or <code>false</code>. For JSON: valid JSON string.</div>
                            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control"
                                   value="{{ old('description', $setting->description) }}"
                                   placeholder="Human-readable description">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.platform-settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ $isEdit ? 'Update Setting' : 'Create Setting' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('dashui/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
