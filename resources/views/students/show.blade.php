@extends('layouts.dashui')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h3 class="mb-1">{{ $student->full_name }}</h3>
                    <p class="mb-0 text-muted">Admission: {{ $student->admission_number }} | Student ID: {{ $student->student_id_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    @can('students.manage')
                        <a href="{{ route('tenant.students.edit', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="btn btn-outline-primary">Edit Profile</a>
                    @endcan
                    <a href="{{ route('tenant.students.index', ['school_slug' => $school->slug]) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light-primary text-primary fs-6">Admission: {{ ucfirst($student->admission_status) }}</span>
                            <span class="badge bg-light-secondary text-secondary fs-6">Lifecycle: {{ ucwords(str_replace('_', ' ', $student->lifecycle_status)) }}</span>
                        </div>
                        <div class="text-muted small mt-2">Admission workflow: Applied -> Admitted -> Enrolled</div>
                        <div class="text-muted small">Lifecycle completion: Promote/Repeat/Transfer/Graduate -> Exit Clearance</div>
                    </div>
                    @can('students.manage')
                        <div class="d-flex gap-2">
                            @if ($student->admission_status === 'applied')
                                <form method="POST" action="{{ route('tenant.students.transition', ['school_slug' => $school->slug, 'student' => $student->id]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="admitted">
                                    <button class="btn btn-sm btn-primary">Mark Admitted</button>
                                </form>
                            @elseif ($student->admission_status === 'admitted')
                                <form method="POST" action="{{ route('tenant.students.transition', ['school_slug' => $school->slug, 'student' => $student->id]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="enrolled">
                                    <button class="btn btn-sm btn-success">Mark Enrolled</button>
                                </form>
                            @endif
                            @if ($student->clearance_completed_at)
                                <span class="badge bg-light-success text-success align-self-center">Clearance Completed {{ $student->clearance_completed_at->format('d M Y H:i') }}</span>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Bio & Contacts</h4></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Gender:</strong> {{ $student->gender ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>DOB:</strong> {{ $student->date_of_birth?->format('d M Y') ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $student->email ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $student->phone ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Address:</strong> {{ $student->address ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>City/Country:</strong> {{ trim(($student->city ?: '') . ' ' . ($student->country ?: '')) ?: 'N/A' }}</p>
                    <p class="mb-0"><strong>Emergency:</strong> {{ $student->emergency_contact_name ?: 'N/A' }} {{ $student->emergency_contact_phone ? '(' . $student->emergency_contact_phone . ')' : '' }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Medical</h4></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Blood Group:</strong> {{ $student->blood_group ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Conditions:</strong> {{ $student->medical_conditions ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Allergies:</strong> {{ $student->allergies ?: 'N/A' }}</p>
                    <p class="mb-0"><strong>Notes:</strong> {{ $student->medical_notes ?: 'N/A' }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Previous School</h4></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $student->previous_school_name ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Address:</strong> {{ $student->previous_school_address ?: 'N/A' }}</p>
                    <p class="mb-0"><strong>Notes:</strong> {{ $student->previous_school_notes ?: 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Class Assignment</h4></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Academic Year:</strong> {{ $student->academicYear?->name ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Course/Class:</strong> {{ $student->course?->name ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Batch/Stream:</strong> {{ $student->batch?->name ?: 'N/A' }}</p>
                    <p class="mb-1"><strong>Admission Date:</strong> {{ $student->admission_date?->format('d M Y') ?: 'N/A' }}</p>
                    <p class="mb-0"><strong>Enrollment Date:</strong> {{ $student->enrollment_date?->format('d M Y') ?: 'N/A' }}</p>
                </div>
            </div>

            @can('students.manage')
                <div class="card mb-4">
                    <div class="card-header"><h4 class="mb-0">Lifecycle Actions</h4></div>
                    <div class="card-body">
                        <h6>Promotion</h6>
                        <form method="POST" action="{{ route('tenant.students.lifecycle.promote', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-4">
                                <select name="academic_year_id" class="form-select form-select-sm" required>
                                    <option value="">Academic Year</option>
                                    @foreach ($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="course_id" class="form-select form-select-sm" required>
                                    <option value="">Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="batch_id" class="form-select form-select-sm">
                                    <option value="">Batch (Optional)</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12"><input class="form-control form-control-sm" name="notes" placeholder="Notes (optional)"></div>
                            <div class="col-12 d-grid"><button class="btn btn-sm btn-primary" type="submit">Promote Student</button></div>
                        </form>

                        <h6>Repeat Class</h6>
                        <form method="POST" action="{{ route('tenant.students.lifecycle.repeat', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-6">
                                <select name="academic_year_id" class="form-select form-select-sm" required>
                                    <option value="">Academic Year</option>
                                    @foreach ($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="batch_id" class="form-select form-select-sm">
                                    <option value="">Batch (Optional)</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12"><input class="form-control form-control-sm" name="notes" placeholder="Reason / notes"></div>
                            <div class="col-12 d-grid"><button class="btn btn-sm btn-warning" type="submit">Mark Repeating</button></div>
                        </form>

                        <h6>Transfer</h6>
                        <form method="POST" action="{{ route('tenant.students.lifecycle.transfer', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-8"><input class="form-control form-control-sm" name="transfer_destination" placeholder="Destination school" required></div>
                            <div class="col-md-4"><input class="form-control form-control-sm" type="date" name="transfer_date"></div>
                            <div class="col-12"><input class="form-control form-control-sm" name="notes" placeholder="Notes"></div>
                            <div class="col-12 d-grid"><button class="btn btn-sm btn-outline-primary" type="submit">Record Transfer</button></div>
                        </form>

                        <h6>Graduate + Alumni</h6>
                        <form method="POST" action="{{ route('tenant.students.lifecycle.graduate', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2 mb-3">
                            @csrf
                            <div class="col-md-4"><input class="form-control form-control-sm" name="graduation_year" type="number" min="1990" max="2100" value="{{ now()->year }}" required></div>
                            <div class="col-md-8"><input class="form-control form-control-sm" name="current_company" placeholder="Current company (optional)"></div>
                            <div class="col-md-6"><input class="form-control form-control-sm" name="current_designation" placeholder="Designation (optional)"></div>
                            <div class="col-md-6"><input class="form-control form-control-sm" name="linkedin_url" placeholder="LinkedIn URL (optional)"></div>
                            <div class="col-12"><input class="form-control form-control-sm" name="achievements" placeholder="Achievements (optional)"></div>
                            <div class="col-12 d-grid"><button class="btn btn-sm btn-success" type="submit">Graduate Student</button></div>
                        </form>

                        <h6>Exit Initiation</h6>
                        <form method="POST" action="{{ route('tenant.students.lifecycle.exit.initiate', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2">
                            @csrf
                            <div class="col-md-6">
                                <select name="exit_reason" class="form-select form-select-sm" required>
                                    <option value="">Exit Reason</option>
                                    @foreach (['graduated', 'transferred', 'dropout', 'expelled', 'deceased', 'other'] as $reason)
                                        <option value="{{ $reason }}">{{ ucfirst($reason) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><input class="form-control form-control-sm" type="date" name="exit_date"></div>
                            <div class="col-12"><input class="form-control form-control-sm" name="exit_notes" placeholder="Exit notes"></div>
                            <div class="col-12 d-grid"><button class="btn btn-sm btn-outline-danger" type="submit">Initiate Exit</button></div>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Clearance Workflow</h4>
                    @can('students.manage')
                        <form method="POST" action="{{ route('tenant.students.lifecycle.exit.complete', ['school_slug' => $school->slug, 'student' => $student->id]) }}">
                            @csrf
                            <button class="btn btn-sm btn-success" type="submit">Complete Exit</button>
                        </form>
                    @endcan
                </div>
                <div class="card-body">
                    @foreach ($student->clearances as $clearance)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-semibold">{{ ucfirst($clearance->department) }}</div>
                                <span class="badge {{ $clearance->status === 'pending' ? 'bg-light-warning text-warning' : 'bg-light-success text-success' }}">{{ ucfirst($clearance->status) }}</span>
                            </div>
                            <div class="small text-muted mb-2">
                                {{ $clearance->remarks ?: 'No remarks' }}
                                @if ($clearance->clearedBy)
                                    | by {{ $clearance->clearedBy->name }} on {{ $clearance->cleared_at?->format('d M Y H:i') }}
                                @endif
                            </div>
                            @can('students.manage')
                                <form method="POST" action="{{ route('tenant.students.lifecycle.clearances.update', ['school_slug' => $school->slug, 'student' => $student->id, 'clearance' => $clearance->id]) }}" class="row g-2">
                                    @csrf
                                    <div class="col-md-4">
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach (['pending', 'cleared', 'waived'] as $status)
                                                <option value="{{ $status }}" @selected($clearance->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <input class="form-control form-control-sm" name="remarks" value="{{ $clearance->remarks }}" placeholder="Remarks">
                                    </div>
                                    <div class="col-12 d-grid"><button class="btn btn-sm btn-outline-secondary">Update</button></div>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Guardians / Parents</h4></div>
                <div class="card-body">
                    @forelse ($student->guardians as $guardian)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-semibold">{{ $guardian->full_name }}</div>
                            <div class="small text-muted">{{ $guardian->pivot->relationship ?: $guardian->relationship ?: 'Guardian' }}</div>
                            <div class="small">{{ $guardian->phone ?: 'No phone' }} {{ $guardian->email ? '| ' . $guardian->email : '' }}</div>
                        </div>
                    @empty
                        <p class="text-muted">No guardians linked yet.</p>
                    @endforelse

                    @can('students.manage')
                        <hr>
                        <h5 class="mb-3">Link New Guardian</h5>
                        <form method="POST" action="{{ route('tenant.students.guardians.store', ['school_slug' => $school->slug, 'student' => $student->id]) }}" class="row g-2">
                            @csrf
                            <div class="col-md-6"><input class="form-control" name="full_name" placeholder="Full name" required></div>
                            <div class="col-md-6"><input class="form-control" name="relationship" placeholder="Relationship"></div>
                            <div class="col-md-6"><input class="form-control" name="phone" placeholder="Phone"></div>
                            <div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email"></div>
                            <div class="col-md-6"><input class="form-control" name="occupation" placeholder="Occupation"></div>
                            <div class="col-md-6"><input class="form-control" name="address" placeholder="Address"></div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" id="is_primary_contact" type="checkbox" name="is_primary_contact" value="1">
                                    <label class="form-check-label" for="is_primary_contact">Primary contact</label>
                                </div>
                            </div>
                            <div class="col-12"><textarea class="form-control" name="notes" rows="2" placeholder="Notes"></textarea></div>
                            <div class="col-12 d-grid"><button class="btn btn-outline-primary" type="submit">Add Guardian</button></div>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Documents</h4></div>
                <div class="card-body">
                    @forelse ($student->documents as $document)
                        <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2">
                            <div>
                                <div class="fw-semibold">{{ $document->document_type }}</div>
                                <small class="text-muted">{{ $document->file_name }}</small>
                            </div>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ asset('storage/' . $document->file_path) }}">Open</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No documents uploaded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
