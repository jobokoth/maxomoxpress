<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Settings | {{ $school->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:860px;">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-3 bg-primary d-flex align-items-center justify-content-center text-white"
             style="width:48px;height:48px;font-size:1.4rem;">
            <i class="bi bi-credit-card-2-front"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold">Payment Settings</h4>
            <div class="text-muted small">{{ $school->name }} &mdash; Configure how parents pay fees</div>
        </div>
    </div>

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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.settings.payments.update', request()->route('school_slug')) }}">
        @csrf

        {{-- ═══════════════════════════════════════════ MPESA ════════════════════════════════════════════ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white fw-semibold">
                <i class="bi bi-phone me-2"></i>Mpesa Configuration
            </div>
            <div class="card-body">

                @if ($config->mpesa_urls_registered)
                    <div class="alert alert-success py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        Safaricom URLs registered successfully on {{ $config->mpesa_urls_registered_at?->format('d M Y H:i') }}.
                        <form method="POST"
                              action="{{ route('tenant.settings.payments.register-urls', request()->route('school_slug')) }}"
                              class="ms-auto">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Re-register URLs</button>
                        </form>
                    </div>
                @elseif ($config->mpesa_mode !== 'disabled' && $config->mpesa_mode !== null && $config->mpesa_mode !== 'platform')
                    <div class="alert alert-warning py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        Safaricom validation/confirmation URLs not yet registered.
                        Save your Daraja credentials below to trigger registration automatically.
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mpesa Mode</label>
                    <select name="mpesa_mode" id="mpesa_mode" class="form-select">
                        <option value="disabled" {{ old('mpesa_mode', $config->mpesa_mode) === 'disabled' ? 'selected' : '' }}>
                            Disabled
                        </option>
                        <option value="own_daraja" {{ old('mpesa_mode', $config->mpesa_mode) === 'own_daraja' ? 'selected' : '' }}>
                            Own Paybill (School's own Daraja account)
                        </option>
                        <option value="bank_paybill" {{ old('mpesa_mode', $config->mpesa_mode) === 'bank_paybill' ? 'selected' : '' }}>
                            Bank Paybill (School's bank-issued Mpesa shortcode)
                        </option>
                        <option value="platform" {{ old('mpesa_mode', $config->mpesa_mode) === 'platform' ? 'selected' : '' }}>
                            MasomoPlus Platform Account (share our Daraja)
                        </option>
                    </select>
                    <div class="form-text">
                        <strong>Own Paybill</strong>: payments go directly to your school's Mpesa account.<br>
                        <strong>Bank Paybill</strong>: payments go to your bank-issued Mpesa till/paybill.<br>
                        <strong>Platform</strong>: we collect and remit — no Daraja setup needed on your side.
                    </div>
                </div>

                <div id="mpesa_fields">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Paybill / Shortcode Number</label>
                            <input type="text" name="mpesa_shortcode" class="form-control"
                                   value="{{ old('mpesa_shortcode', $config->mpesa_shortcode) }}"
                                   placeholder="e.g. 400200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Reference
                                <span class="text-muted small">(shown on customer's phone)</span>
                            </label>
                            <input type="text" name="mpesa_account_reference" class="form-control"
                                   value="{{ old('mpesa_account_reference', $config->mpesa_account_reference) }}"
                                   placeholder="e.g. School Fees" maxlength="100">
                        </div>
                    </div>

                    <div id="daraja_credentials" class="mt-3 p-3 border rounded bg-light">
                        <div class="fw-semibold mb-2">
                            <i class="bi bi-key me-1 text-warning"></i>Daraja API Credentials
                            <span class="badge bg-danger ms-1">Required for Own/Bank Paybill</span>
                        </div>
                        <div class="form-text mb-3">
                            Get these from <strong>developer.safaricom.co.ke</strong> under your app.
                            Leave blank to keep existing credentials unchanged.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Consumer Key</label>
                                <input type="password" name="consumer_key" class="form-control"
                                       autocomplete="new-password"
                                       placeholder="{{ $cred ? '••••••••••••••••' : 'Enter Consumer Key' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Consumer Secret</label>
                                <input type="password" name="consumer_secret" class="form-control"
                                       autocomplete="new-password"
                                       placeholder="{{ $cred ? '••••••••••••••••' : 'Enter Consumer Secret' }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Lipa Na Mpesa Passkey</label>
                                <input type="password" name="passkey" class="form-control"
                                       autocomplete="new-password"
                                       placeholder="{{ $cred?->passkey ? '••••••••••••••••' : 'Enter Passkey (for STK Push)' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Environment</label>
                                <select name="environment" class="form-select">
                                    <option value="production" {{ old('environment', $cred?->environment ?? 'production') === 'production' ? 'selected' : '' }}>
                                        Production
                                    </option>
                                    <option value="sandbox" {{ old('environment', $cred?->environment) === 'sandbox' ? 'selected' : '' }}>
                                        Sandbox (Testing)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-white border rounded small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Webhook URLs (auto-registered on save):</strong><br>
                            Validation: <code>{{ route('mpesa.validation', request()->route('school_slug')) }}</code><br>
                            Confirmation: <code>{{ route('mpesa.confirmation', request()->route('school_slug')) }}</code><br>
                            STK Callback: <code>{{ route('mpesa.stk.callback') }}</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════ BANK TRANSFER ═══════════════════════════════════════ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-bank me-2"></i>Bank Transfer
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="bank_transfer_enabled"
                           name="bank_transfer_enabled" value="1"
                           {{ old('bank_transfer_enabled', $config->bank_transfer_enabled) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="bank_transfer_enabled">
                        Accept bank transfers (RTGS / SWIFT / Pesalink)
                    </label>
                </div>

                <div id="bank_fields">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control"
                                   value="{{ old('bank_name', $config->bank_name) }}"
                                   placeholder="e.g. Equity Bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="bank_account_name" class="form-control"
                                   value="{{ old('bank_account_name', $config->bank_account_name) }}"
                                   placeholder="As it appears on bank statement">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control"
                                   value="{{ old('bank_account_number', $config->bank_account_number) }}"
                                   placeholder="e.g. 0123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input type="text" name="bank_branch" class="form-control"
                                   value="{{ old('bank_branch', $config->bank_branch) }}"
                                   placeholder="e.g. Westlands Branch">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">SWIFT Code <span class="text-muted">(for int'l transfers)</span></label>
                            <input type="text" name="bank_swift_code" class="form-control"
                                   value="{{ old('bank_swift_code', $config->bank_swift_code) }}"
                                   placeholder="e.g. EQBLKENA">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Accepted Transfer Types</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="accepts_rtgs" value="1"
                                           id="rtgs" {{ old('accepts_rtgs', $config->accepts_rtgs) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rtgs">RTGS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="accepts_pesalink" value="1"
                                           id="pesalink" {{ old('accepts_pesalink', $config->accepts_pesalink) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pesalink">Pesalink</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="accepts_swift" value="1"
                                           id="swift" {{ old('accepts_swift', $config->accepts_swift) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="swift">SWIFT</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════ CHEQUES ══════════════════════════════════════════ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2"></i>Cheques
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="cheques_enabled"
                           name="cheques_enabled" value="1"
                           {{ old('cheques_enabled', $config->cheques_enabled) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="cheques_enabled">Accept cheque payments</label>
                </div>
                <div>
                    <label class="form-label">Make cheques payable to</label>
                    <input type="text" name="cheques_payable_to" class="form-control"
                           value="{{ old('cheques_payable_to', $config->cheques_payable_to) }}"
                           placeholder="e.g. Greenwood Academy Ltd" maxlength="150">
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════ CASH ══════════════════════════════════════════ --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="cash_enabled"
                           name="cash_enabled" value="1"
                           {{ old('cash_enabled', $config->cash_enabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="cash_enabled">
                        <i class="bi bi-cash me-1"></i>Accept cash payments
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i>Save Payment Settings
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modeSelect = document.getElementById('mpesa_mode');
    const mpesaFields = document.getElementById('mpesa_fields');
    const darajaFields = document.getElementById('daraja_credentials');

    function updateMpesaVisibility() {
        const mode = modeSelect.value;
        mpesaFields.style.display = mode === 'disabled' ? 'none' : '';
        darajaFields.style.display = (mode === 'own_daraja' || mode === 'bank_paybill') ? '' : 'none';
    }

    modeSelect.addEventListener('change', updateMpesaVisibility);
    updateMpesaVisibility();
</script>
</body>
</html>
