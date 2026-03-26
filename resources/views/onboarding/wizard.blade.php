<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>School Setup | MasomoPlus School ERP</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: calc(50% + 20px);
            width: calc(100% - 40px);
            height: 2px;
            background: #dee2e6;
        }
        .step-item.completed::after { background: #0d6efd; }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            border: 2px solid #dee2e6;
            background: #fff;
            color: #6c757d;
            position: relative;
            z-index: 1;
        }
        .step-item.completed .step-circle {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .step-item.active .step-circle {
            border-color: #0d6efd;
            color: #0d6efd;
            font-weight: 700;
        }
        .step-label {
            font-size: 0.72rem;
            margin-top: 0.35rem;
            color: #6c757d;
            text-align: center;
            white-space: nowrap;
        }
        .step-item.active .step-label { color: #0d6efd; font-weight: 600; }
        .step-item.completed .step-label { color: #0d6efd; }
        .step-connector { flex: 1; height: 2px; background: #dee2e6; min-width: 30px; }
        .step-connector.done { background: #0d6efd; }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">

                {{-- Header --}}
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1">Welcome to MasomoPlus School ERP</h2>
                    <p class="text-muted">Let's set up <strong>{{ $school->name ?: 'your school' }}</strong> in a few quick steps.</p>
                </div>

                {{-- Step indicator --}}
                @php
                    $steps = [
                        1 => 'School Info',
                        2 => 'Academic',
                        3 => 'Admin User',
                        4 => 'Payments',
                        5 => 'Branding',
                    ];
                @endphp
                <div class="d-flex align-items-center justify-content-center mb-5 gap-1">
                    @foreach ($steps as $num => $label)
                        @php
                            $state = $num < $step ? 'completed' : ($num === $step ? 'active' : '');
                        @endphp
                        <div class="step-item {{ $state }}">
                            <div class="step-circle">
                                @if ($num < $step)
                                    <i class="bi bi-check-lg"></i>
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            <div class="step-label">{{ $label }}</div>
                        </div>
                        @if (!$loop->last)
                            <div class="step-connector {{ $num < $step ? 'done' : '' }}" style="margin-bottom:1.4rem;"></div>
                        @endif
                    @endforeach
                </div>

                {{-- Alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Step content --}}
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">

                        {{-- ======================== STEP 1 ======================== --}}
                        @if ($step === 1)
                            <h4 class="fw-bold mb-1">School Information</h4>
                            <p class="text-muted mb-4">Tell us about your school so we can personalise your experience.</p>

                            <form method="POST" action="{{ route('onboarding.step') }}">
                                @csrf
                                <input type="hidden" name="step" value="1">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $school->name) }}"
                                               id="schoolName" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">School URL Slug <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text text-muted small">masomo.app/s/</span>
                                            <input type="text" name="slug" id="schoolSlug"
                                                   class="form-control @error('slug') is-invalid @enderror"
                                                   value="{{ old('slug', $school->slug) }}"
                                                   pattern="[a-z0-9\-]+" required>
                                        </div>
                                        <div class="form-text">Lowercase letters, numbers, and hyphens only.</div>
                                        @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email', $school->email) }}">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control"
                                               value="{{ old('phone', $school->phone) }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control"
                                               value="{{ old('address', $school->address) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control"
                                               value="{{ old('city', $school->city) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control"
                                               value="{{ old('country', $school->country ?? 'Kenya') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Timezone</label>
                                        <input type="text" name="timezone" class="form-control"
                                               value="{{ old('timezone', $school->timezone ?? 'Africa/Nairobi') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Currency</label>
                                        <input type="text" name="currency" class="form-control" maxlength="12"
                                               value="{{ old('currency', $school->currency ?? 'KES') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Website</label>
                                        <input type="url" name="website" class="form-control"
                                               placeholder="https://"
                                               value="{{ old('website', $school->website) }}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        Save & Continue <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </form>

                        {{-- ======================== STEP 2 ======================== --}}
                        @elseif ($step === 2)
                            <h4 class="fw-bold mb-1">Academic Structure</h4>
                            <p class="text-muted mb-4">Set up your first academic year and terms.</p>

                            <form method="POST" action="{{ route('onboarding.step') }}" id="academicForm">
                                @csrf
                                <input type="hidden" name="step" value="2">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Academic Year Name <span class="text-danger">*</span></label>
                                        <input type="text" name="academic_year_name" class="form-control @error('academic_year_name') is-invalid @enderror"
                                               placeholder="e.g. 2025/2026"
                                               value="{{ old('academic_year_name') }}" required>
                                        @error('academic_year_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="academic_year_start" class="form-control @error('academic_year_start') is-invalid @enderror"
                                               value="{{ old('academic_year_start') }}" required>
                                        @error('academic_year_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="academic_year_end" class="form-control @error('academic_year_end') is-invalid @enderror"
                                               value="{{ old('academic_year_end') }}" required>
                                        @error('academic_year_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Number of Terms <span class="text-danger">*</span></label>
                                        <select name="number_of_terms" id="termCount" class="form-select" required>
                                            <option value="2" {{ old('number_of_terms', '3') == '2' ? 'selected' : '' }}>2 Terms</option>
                                            <option value="3" {{ old('number_of_terms', '3') == '3' ? 'selected' : '' }}>3 Terms</option>
                                        </select>
                                    </div>

                                    <div class="col-12" id="termsContainer">
                                        @php $numTerms = old('number_of_terms', 3); @endphp
                                        @for ($i = 0; $i < $numTerms; $i++)
                                            <div class="border rounded p-3 mb-3 term-block">
                                                <h6 class="fw-semibold mb-3">Term {{ $i + 1 }}</h6>
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" name="term_names[]" class="form-control"
                                                               value="{{ old("term_names.$i", 'Term ' . ($i + 1)) }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" name="term_starts[]" class="form-control"
                                                               value="{{ old("term_starts.$i") }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" name="term_ends[]" class="form-control"
                                                               value="{{ old("term_ends.$i") }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        Save & Continue <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </form>

                        {{-- ======================== STEP 3 ======================== --}}
                        @elseif ($step === 3)
                            <h4 class="fw-bold mb-1">Confirm Admin Account</h4>
                            <p class="text-muted mb-4">You will be the school admin. Confirm your details below.</p>

                            <form method="POST" action="{{ route('onboarding.step') }}">
                                @csrf
                                <input type="hidden" name="step" value="3">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', auth()->user()->name) }}" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control bg-light" value="{{ auth()->user()->email }}" disabled readonly>
                                        <div class="form-text">Email address cannot be changed here.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control"
                                               value="{{ old('phone', auth()->user()->phone) }}">
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-primary d-flex align-items-center gap-2 mb-0" role="alert">
                                            <i class="bi bi-shield-check fs-5"></i>
                                            <div>
                                                You will be assigned the <strong>school_admin</strong> role, giving you full access to manage <strong>{{ $school->name }}</strong>.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        Confirm & Continue <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </form>

                        {{-- ======================== STEP 4 ======================== --}}
                        @elseif ($step === 4)
                            <h4 class="fw-bold mb-1">Payment Setup</h4>
                            <p class="text-muted mb-4">Configure how parents can pay fees. You can skip this and set it up later.</p>

                            <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
                                <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                                <div>
                                    <strong>Note:</strong> Full Mpesa integration including automated URL registration will be configured in the payment settings after setup.
                                </div>
                            </div>

                            <form method="POST" action="{{ route('onboarding.step') }}">
                                @csrf
                                <input type="hidden" name="step" value="4">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Mpesa Paybill Number</label>
                                        <input type="text" name="mpesa_paybill" class="form-control"
                                               placeholder="e.g. 123456"
                                               value="{{ old('mpesa_paybill', ($school->settings['payment']['mpesa_paybill'] ?? '')) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control"
                                               value="{{ old('bank_name', ($school->settings['payment']['bank_name'] ?? '')) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Bank Account Number</label>
                                        <input type="text" name="bank_account_number" class="form-control"
                                               value="{{ old('bank_account_number', ($school->settings['payment']['bank_account_number'] ?? '')) }}">
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="acceptCheques" name="accept_cheques" value="1"
                                                {{ old('accept_cheques', ($school->settings['payment']['accept_cheques'] ?? false)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="acceptCheques">Accept Cheque Payments</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="submit" name="skip" value="1" class="btn btn-outline-secondary px-4">
                                        Skip for Now
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        Save & Continue <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </form>

                        {{-- ======================== STEP 5 ======================== --}}
                        @elseif ($step === 5)
                            <h4 class="fw-bold mb-1">School Branding</h4>
                            <p class="text-muted mb-4">Upload your school logo and cover image. You can skip this and add them later.</p>

                            <form method="POST" action="{{ route('onboarding.step') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="step" value="5">

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">School Logo</label>
                                        @if ($school->logo)
                                            <div class="mb-2">
                                                <img src="{{ $school->logo }}" alt="Current Logo" class="img-thumbnail" style="max-height:80px;">
                                                <div class="form-text">Current logo. Upload a new one to replace it.</div>
                                            </div>
                                        @endif
                                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                        <div class="form-text">Recommended: square PNG/SVG, max 2 MB.</div>
                                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Cover Image</label>
                                        @if ($school->cover_image)
                                            <div class="mb-2">
                                                <img src="{{ $school->cover_image }}" alt="Current Cover" class="img-thumbnail" style="max-height:80px;">
                                                <div class="form-text">Current cover image. Upload a new one to replace it.</div>
                                            </div>
                                        @endif
                                        <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                                        <div class="form-text">Recommended: 16:9 landscape, max 5 MB.</div>
                                        @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="submit" name="skip" value="1" class="btn btn-outline-secondary px-4">
                                        Skip & Finish
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        Upload & Finish <i class="bi bi-check-circle ms-1"></i>
                                    </button>
                                </div>
                            </form>
                        @endif

                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Step {{ $step }} of 5 &mdash; <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">Sign out</a>
                </p>
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate slug from school name on step 1
        const nameInput  = document.getElementById('schoolName');
        const slugInput  = document.getElementById('schoolSlug');

        if (nameInput && slugInput) {
            let slugManuallyEdited = slugInput.value.length > 0;

            slugInput.addEventListener('input', () => { slugManuallyEdited = true; });

            nameInput.addEventListener('input', () => {
                if (slugManuallyEdited) return;
                slugInput.value = nameInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            });
        }

        // Dynamically render term blocks based on term count selector
        const termCountSel   = document.getElementById('termCount');
        const termsContainer = document.getElementById('termsContainer');

        if (termCountSel && termsContainer) {
            termCountSel.addEventListener('change', () => {
                const count = parseInt(termCountSel.value, 10);
                const blocks = termsContainer.querySelectorAll('.term-block');

                // Add missing blocks
                while (termsContainer.querySelectorAll('.term-block').length < count) {
                    const idx = termsContainer.querySelectorAll('.term-block').length;
                    const div = document.createElement('div');
                    div.className = 'border rounded p-3 mb-3 term-block';
                    div.innerHTML = `
                        <h6 class="fw-semibold mb-3">Term ${idx + 1}</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="term_names[]" class="form-control" value="Term ${idx + 1}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="term_starts[]" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="term_ends[]" class="form-control" required>
                            </div>
                        </div>`;
                    termsContainer.appendChild(div);
                }

                // Remove extra blocks
                const allBlocks = termsContainer.querySelectorAll('.term-block');
                for (let i = count; i < allBlocks.length; i++) {
                    allBlocks[i].remove();
                }
            });
        }
    </script>
</body>

</html>
