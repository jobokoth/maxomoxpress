@php
    $isEdit = $student->exists;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Admission Details</h4></div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Middle Name</label>
            <input name="middle_name" class="form-control" value="{{ old('middle_name', $student->middle_name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('gender', $student->gender) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Admission Number</label>
            <input name="admission_number" class="form-control" value="{{ old('admission_number', $student->admission_number) }}" placeholder="Auto-generated if empty">
        </div>
        <div class="col-md-3">
            <label class="form-label">Student ID</label>
            <input name="student_id_number" class="form-control" value="{{ old('student_id_number', $student->student_id_number) }}" placeholder="Auto-generated if empty">
        </div>

        <div class="col-md-4">
            <label class="form-label">Academic Year</label>
            <select name="academic_year_id" class="form-select">
                <option value="">Select</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $student->academic_year_id) === (string) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Course / Class</label>
            <select name="course_id" class="form-select">
                <option value="">Select</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) old('course_id', $student->course_id) === (string) $course->id)>{{ $course->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Batch / Stream</label>
            <select name="batch_id" class="form-select">
                <option value="">Select</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" @selected((string) old('batch_id', $student->batch_id) === (string) $batch->id)>{{ $batch->name }}{{ $batch->course ? ' (' . $batch->course->name . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Contacts</h4></div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input name="city" class="form-control" value="{{ old('city', $student->city) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Address</label>
            <input name="address" class="form-control" value="{{ old('address', $student->address) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Country</label>
            <input name="country" class="form-control" value="{{ old('country', $student->country) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Emergency Contact Name</label>
            <input name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Emergency Contact Phone</label>
            <input name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}">
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Medical</h4></div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">Blood Group</label>
            <input name="blood_group" class="form-control" value="{{ old('blood_group', $student->blood_group) }}">
        </div>
        <div class="col-md-9">
            <label class="form-label">Medical Conditions</label>
            <input name="medical_conditions" class="form-control" value="{{ old('medical_conditions', $student->medical_conditions) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Allergies</label>
            <textarea name="allergies" class="form-control" rows="2">{{ old('allergies', $student->allergies) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Medical Notes</label>
            <textarea name="medical_notes" class="form-control" rows="2">{{ old('medical_notes', $student->medical_notes) }}</textarea>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Previous School</h4></div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">School Name</label>
            <input name="previous_school_name" class="form-control" value="{{ old('previous_school_name', $student->previous_school_name) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">School Address</label>
            <input name="previous_school_address" class="form-control" value="{{ old('previous_school_address', $student->previous_school_address) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="previous_school_notes" class="form-control" rows="2">{{ old('previous_school_notes', $student->previous_school_notes) }}</textarea>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Primary Guardian (Optional)</h4></div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">Full Name</label>
            <input name="guardian_full_name" class="form-control" value="{{ old('guardian_full_name') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="guardian_email" class="form-control" value="{{ old('guardian_email') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Relationship</label>
            <input name="guardian_relationship" class="form-control" value="{{ old('guardian_relationship') }}" placeholder="e.g. Mother">
        </div>
        <div class="col-md-4">
            <label class="form-label">Occupation</label>
            <input name="guardian_occupation" class="form-control" value="{{ old('guardian_occupation') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Address</label>
            <input name="guardian_address" class="form-control" value="{{ old('guardian_address') }}">
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h4 class="mb-0">Document Uploads</h4></div>
    <div class="card-body">
        <div class="row g-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="col-md-4">
                    <label class="form-label">Document Type</label>
                    <input name="document_types[]" class="form-control" value="{{ old('document_types.' . $i) }}" placeholder="e.g. Birth Certificate">
                </div>
                <div class="col-md-8">
                    <label class="form-label">File</label>
                    <input type="file" name="documents[]" class="form-control">
                </div>
            @endfor
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Student' : 'Create Admission' }}</button>
    <a href="{{ route('tenant.students.index', ['school_slug' => $school->slug]) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
