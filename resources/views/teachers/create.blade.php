@extends('layouts.dashui')

@section('content')
    <div class="row mt-4 justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="{{ route('tenant.teachers.index', ['school_slug' => $school->slug]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h3 class="mb-0">Add Teacher</h3>
                    <p class="text-muted mb-0 small">Create a login account for a new teacher.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tenant.teachers.store', ['school_slug' => $school->slug]) }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="e.g. John Kamau"
                                       required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="e.g. j.kamau@school.ac.ke"
                                       required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone"
                                       class="form-control"
                                       value="{{ old('phone') }}"
                                       placeholder="e.g. +254712345678">
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

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
                        </div>

                        <div class="alert alert-info d-flex align-items-center gap-2 mt-4 mb-0" role="alert">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <div class="small">
                                The teacher will log in with this email and password. They will be assigned the <strong>teacher</strong> role with access to attendance, timetable, and marks.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tenant.teachers.index', ['school_slug' => $school->slug]) }}"
                               class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-person-plus me-1"></i>Create Teacher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
