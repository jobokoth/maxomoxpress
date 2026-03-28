<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Register Your School | MasomoXpress School ERP</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .brand-gradient {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        }
    </style>
</head>

<body class="bg-light">
    <main>
        <section class="container d-flex flex-column min-vh-100 py-5">
            <div class="row align-items-center justify-content-center g-0 flex-grow-1">
                <div class="col-12 col-md-10 col-lg-8 col-xl-6">

                    {{-- Branding --}}
                    <div class="text-center mb-4">
                        <div class="brand-gradient d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                             style="width:56px;height:56px;">
                            <i class="bi bi-mortarboard-fill text-white fs-3"></i>
                        </div>
                        <h1 class="fw-bold mb-1">MasomoXpress School ERP</h1>
                        <p class="text-muted">Get started in minutes. Free {{ config('app.trial_days', 30) }}-day trial, no credit card required.</p>
                    </div>

                    {{-- Errors --}}
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
                        <div class="card-body p-4 p-md-5">
                            <h4 class="fw-bold mb-1">Create your school account</h4>
                            <p class="text-muted mb-4">Fill in the details below to get started.</p>

                            <form method="POST" action="{{ route('school.register.store') }}">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Your Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="your_name"
                                               class="form-control @error('your_name') is-invalid @enderror"
                                               value="{{ old('your_name') }}"
                                               placeholder="e.g. Jane Wanjiru"
                                               required autofocus>
                                        @error('your_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}"
                                               placeholder="you@school.ac.ke"
                                               required>
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Min 8 characters"
                                               required>
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation"
                                               class="form-control"
                                               placeholder="Repeat password"
                                               required>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-1">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name"
                                               class="form-control @error('school_name') is-invalid @enderror"
                                               value="{{ old('school_name') }}"
                                               placeholder="e.g. Greenwood Academy"
                                               required>
                                        <div class="form-text">You can update this and all other school details during setup.</div>
                                        @error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Create Account &amp; Start Setup
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <p class="text-center text-muted small mt-3">
                        Already have an account? <a href="{{ route('login') }}">Sign in</a>
                    </p>

                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
