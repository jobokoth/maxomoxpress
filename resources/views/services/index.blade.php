@extends('layouts.dashui')

@section('content')
<div class="row mt-4 g-3">
    <div class="col-12">
        @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    </div>

    <div class="col-12"><h4 class="mb-0">Student Services Hub</h4><p class="text-muted mb-0">Discipline, clinic, library, transport and hostel operations.</p></div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Discipline Incident</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.services.discipline.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Student</label><select name="student_id" class="form-select form-select-sm" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></div><div class="col"><label class="form-label">Date</label><input type="date" name="incident_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div></div>
                    <div class="row g-2 mb-2"><div class="col"><input name="incident_type" class="form-control form-control-sm" placeholder="Incident type" required></div><div class="col"><select name="severity" class="form-select form-select-sm" required><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div></div>
                    <div class="mb-2"><input name="action_taken" class="form-control form-control-sm" placeholder="Action taken"></div>
                    <div class="mb-2"><input name="remarks" class="form-control form-control-sm" placeholder="Remarks"></div>
                    <button class="btn btn-primary btn-sm" type="submit">Record Incident</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Recent Incidents</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Student</th><th>Type</th><th>Severity</th><th>Status</th></tr></thead><tbody>
                @forelse($incidents as $incident)
                    <tr><td>{{ $incident->incident_date?->format('d M Y') }}</td><td>{{ $incident->student?->full_name }}</td><td>{{ $incident->incident_type }}</td><td>{{ strtoupper($incident->severity) }}</td><td>{{ strtoupper($incident->status) }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No incidents recorded.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $incidents->appends(request()->query())->links() }}</div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Clinic Record</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.services.clinic.store', ['school_slug' => $school->slug]) }}">@csrf
                    <div class="row g-2 mb-2"><div class="col"><label class="form-label">Student</label><select name="student_id" class="form-select form-select-sm" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></div><div class="col"><label class="form-label">Visit Date</label><input type="date" name="visit_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div></div>
                    <div class="row g-2 mb-2"><div class="col"><input name="complaint" class="form-control form-control-sm" placeholder="Complaint" required></div><div class="col"><input name="diagnosis" class="form-control form-control-sm" placeholder="Diagnosis"></div></div>
                    <div class="row g-2 mb-2"><div class="col"><input name="medication" class="form-control form-control-sm" placeholder="Medication"></div><div class="col"><input type="date" name="follow_up_date" class="form-control form-control-sm"></div></div>
                    <div class="mb-2"><textarea name="treatment" class="form-control form-control-sm" rows="2" placeholder="Treatment"></textarea></div>
                    <button class="btn btn-primary btn-sm" type="submit">Save Clinic Record</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Recent Clinic Records</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Date</th><th>Student</th><th>Complaint</th><th>Status</th></tr></thead><tbody>
                @forelse($clinicRecords as $record)
                    <tr><td>{{ $record->visit_date?->format('d M Y') }}</td><td>{{ $record->student?->full_name }}</td><td>{{ $record->complaint }}</td><td>{{ strtoupper($record->status) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No clinic records found.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $clinicRecords->appends(request()->query())->links() }}</div>
        </div>
    </div>

    <div class="col-12"><hr class="my-2"></div>

    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Library: Add Book</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.library.books.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><input name="title" class="form-control form-control-sm" placeholder="Title" required></div>
                <div class="mb-2"><input name="author" class="form-control form-control-sm" placeholder="Author" required></div>
                <div class="mb-2"><input name="isbn" class="form-control form-control-sm" placeholder="ISBN"></div>
                <div class="row g-2 mb-2"><div class="col"><input type="number" min="1" name="copies_total" class="form-control form-control-sm" placeholder="Copies" required></div><div class="col"><input name="location_rack" class="form-control form-control-sm" placeholder="Rack"></div></div>
                <button class="btn btn-primary btn-sm" type="submit">Add Book</button>
            </form>
        </div></div>

        <div class="card"><div class="card-header"><h5 class="mb-0">Library: Issue Book</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.library.issues.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><label class="form-label">Book</label><select name="book_id" class="form-select form-select-sm" required>@foreach($books as $book)<option value="{{ $book->id }}">{{ $book->title }} ({{ $book->copies_available }})</option>@endforeach</select></div>
                <div class="mb-2"><label class="form-label">Student</label><select name="student_id" class="form-select form-select-sm" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></div>
                <div class="row g-2 mb-2"><div class="col"><input type="date" name="issued_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required></div><div class="col"><input type="date" name="due_date" class="form-control form-control-sm" required></div></div>
                <button class="btn btn-warning btn-sm" type="submit">Issue</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Books</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Title</th><th>Author</th><th>Copies</th><th>Status</th></tr></thead><tbody>
                @forelse($books as $book)
                    <tr><td>{{ $book->title }}</td><td>{{ $book->author }}</td><td>{{ $book->copies_available }}/{{ $book->copies_total }}</td><td>{{ strtoupper($book->status) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No books in library.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $books->appends(request()->query())->links() }}</div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Book Issues</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Book</th><th>Student</th><th>Issued</th><th>Due</th><th>Status</th><th>Action</th></tr></thead><tbody>
                @forelse($bookIssues as $issue)
                    <tr>
                        <td>{{ $issue->book?->title }}</td><td>{{ $issue->student?->full_name }}</td><td>{{ $issue->issued_date?->format('d M Y') }}</td><td>{{ $issue->due_date?->format('d M Y') }}</td><td>{{ strtoupper($issue->status) }}</td>
                        <td>
                            @if($issue->status !== 'returned')
                                <form method="POST" action="{{ route('tenant.services.library.issues.return', ['school_slug' => $school->slug, 'issue' => $issue->id]) }}" class="d-flex gap-1">@csrf
                                    <input type="date" name="returned_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                                    <input type="number" name="fine_amount" class="form-control form-control-sm" step="0.01" min="0" placeholder="Fine">
                                    <button class="btn btn-sm btn-success" type="submit">Return</button>
                                </form>
                            @else
                                <span class="text-muted">Completed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No issue records.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="card-footer">{{ $bookIssues->appends(request()->query())->links() }}</div>
        </div>
    </div>

    <div class="col-12"><hr class="my-2"></div>

    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Transport Route</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.transport.routes.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><input name="name" class="form-control form-control-sm" placeholder="Route name" required></div>
                <div class="mb-2"><input name="description" class="form-control form-control-sm" placeholder="Description"></div>
                <div class="row g-2 mb-2"><div class="col"><input type="number" step="0.01" min="0" name="distance_km" class="form-control form-control-sm" placeholder="Km"></div><div class="col"><input type="number" step="0.01" min="0" name="fee" class="form-control form-control-sm" placeholder="Fee"></div></div>
                <button class="btn btn-primary btn-sm" type="submit">Save Route</button>
            </form>
        </div></div>

        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Vehicle</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.transport.vehicles.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><input name="registration_number" class="form-control form-control-sm" placeholder="Registration number" required></div>
                <div class="row g-2 mb-2"><div class="col"><select name="type" class="form-select form-select-sm" required><option value="bus">Bus</option><option value="van">Van</option><option value="car">Car</option></select></div><div class="col"><input type="number" name="capacity" class="form-control form-control-sm" min="1" placeholder="Capacity" required></div></div>
                <div class="mb-2"><select name="driver_user_id" class="form-select form-select-sm"><option value="">No driver</option>@foreach($staffUsers as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                <button class="btn btn-primary btn-sm" type="submit">Add Vehicle</button>
            </form>
        </div></div>

        <div class="card"><div class="card-header"><h5 class="mb-0">Assign Student Transport</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.transport.assignments.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><select name="student_id" class="form-select form-select-sm" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></div>
                <div class="mb-2"><select name="transport_route_id" class="form-select form-select-sm" required>@foreach($routes as $route)<option value="{{ $route->id }}">{{ $route->name }}</option>@endforeach</select></div>
                <div class="mb-2"><select name="vehicle_id" class="form-select form-select-sm"><option value="">No vehicle</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }}</option>@endforeach</select></div>
                <div class="row g-2 mb-2"><div class="col"><input type="date" name="start_date" class="form-control form-control-sm"></div><div class="col"><input type="number" name="fee_amount" class="form-control form-control-sm" min="0" step="0.01" placeholder="Fee"></div></div>
                <button class="btn btn-warning btn-sm" type="submit">Assign Transport</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><h6 class="mb-0">Routes</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Distance</th><th>Fee</th><th>Status</th></tr></thead><tbody>@forelse($routes as $route)<tr><td>{{ $route->name }}</td><td>{{ $route->distance_km }}</td><td>{{ number_format((float)$route->fee, 2) }}</td><td>{{ strtoupper($route->status) }}</td></tr>@empty<tr><td colspan="4" class="text-muted text-center py-3">No routes yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $routes->appends(request()->query())->links() }}</div></div>
        <div class="card mb-3"><div class="card-header"><h6 class="mb-0">Vehicles</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Reg No</th><th>Type</th><th>Capacity</th><th>Driver</th></tr></thead><tbody>@forelse($vehicles as $vehicle)<tr><td>{{ $vehicle->registration_number }}</td><td>{{ strtoupper($vehicle->type) }}</td><td>{{ $vehicle->capacity }}</td><td>{{ $vehicle->driver?->name ?: '-' }}</td></tr>@empty<tr><td colspan="4" class="text-muted text-center py-3">No vehicles yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $vehicles->appends(request()->query())->links() }}</div></div>
        <div class="card"><div class="card-header"><h6 class="mb-0">Student Transport Assignments</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Route</th><th>Vehicle</th><th>Fee</th><th>Status</th></tr></thead><tbody>@forelse($studentTransports as $assignment)<tr><td>{{ $assignment->student?->full_name }}</td><td>{{ $assignment->route?->name }}</td><td>{{ $assignment->vehicle?->registration_number ?: '-' }}</td><td>{{ number_format((float)$assignment->fee_amount, 2) }}</td><td>{{ strtoupper($assignment->status) }}</td></tr>@empty<tr><td colspan="5" class="text-muted text-center py-3">No transport assignments.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $studentTransports->appends(request()->query())->links() }}</div></div>
    </div>

    <div class="col-12"><hr class="my-2"></div>

    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Hostel</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.hostels.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><input name="name" class="form-control form-control-sm" placeholder="Hostel name" required></div>
                <div class="row g-2 mb-2"><div class="col"><select name="type" class="form-select form-select-sm" required><option value="boys">Boys</option><option value="girls">Girls</option><option value="mixed">Mixed</option></select></div><div class="col"><input type="number" name="capacity" class="form-control form-control-sm" min="0" placeholder="Capacity" required></div></div>
                <div class="mb-2"><select name="warden_user_id" class="form-select form-select-sm"><option value="">No warden</option>@foreach($staffUsers as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                <button class="btn btn-primary btn-sm" type="submit">Create Hostel</button>
            </form>
        </div></div>

        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Hostel Room</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.hostel-rooms.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><select name="hostel_id" class="form-select form-select-sm" required>@foreach($hostels as $hostel)<option value="{{ $hostel->id }}">{{ $hostel->name }}</option>@endforeach</select></div>
                <div class="row g-2 mb-2"><div class="col"><input name="room_number" class="form-control form-control-sm" placeholder="Room no" required></div><div class="col"><input type="number" name="capacity" class="form-control form-control-sm" min="1" placeholder="Capacity" required></div></div>
                <div class="row g-2 mb-2"><div class="col"><select name="room_type" class="form-select form-select-sm" required><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div><div class="col"><input type="number" name="monthly_fee" class="form-control form-control-sm" min="0" step="0.01" placeholder="Monthly fee"></div></div>
                <button class="btn btn-primary btn-sm" type="submit">Add Room</button>
            </form>
        </div></div>

        <div class="card"><div class="card-header"><h5 class="mb-0">Allocate Hostel</h5></div><div class="card-body">
            <form method="POST" action="{{ route('tenant.services.hostel-allocations.store', ['school_slug' => $school->slug]) }}">@csrf
                <div class="mb-2"><select name="student_id" class="form-select form-select-sm" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name }}</option>@endforeach</select></div>
                <div class="mb-2"><select name="hostel_id" class="form-select form-select-sm" required>@foreach($hostels as $hostel)<option value="{{ $hostel->id }}">{{ $hostel->name }}</option>@endforeach</select></div>
                <div class="mb-2"><select name="hostel_room_id" class="form-select form-select-sm" required>@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->hostel?->name }} / {{ $room->room_number }} ({{ $room->occupied_beds }}/{{ $room->capacity }})</option>@endforeach</select></div>
                <div class="row g-2 mb-2"><div class="col"><select name="academic_year_id" class="form-select form-select-sm"><option value="">No year</option>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach</select></div><div class="col"><input type="date" name="from_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}"></div></div>
                <button class="btn btn-warning btn-sm" type="submit">Allocate</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><h6 class="mb-0">Hostels</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Type</th><th>Capacity</th><th>Warden</th></tr></thead><tbody>@forelse($hostels as $hostel)<tr><td>{{ $hostel->name }}</td><td>{{ strtoupper($hostel->type) }}</td><td>{{ $hostel->capacity }}</td><td>{{ $hostel->warden?->name ?: '-' }}</td></tr>@empty<tr><td colspan="4" class="text-muted text-center py-3">No hostels yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $hostels->appends(request()->query())->links() }}</div></div>
        <div class="card mb-3"><div class="card-header"><h6 class="mb-0">Rooms</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Hostel</th><th>Room</th><th>Type</th><th>Occupancy</th></tr></thead><tbody>@forelse($rooms as $room)<tr><td>{{ $room->hostel?->name }}</td><td>{{ $room->room_number }}</td><td>{{ strtoupper($room->room_type) }}</td><td>{{ $room->occupied_beds }}/{{ $room->capacity }}</td></tr>@empty<tr><td colspan="4" class="text-muted text-center py-3">No rooms yet.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $rooms->appends(request()->query())->links() }}</div></div>
        <div class="card"><div class="card-header"><h6 class="mb-0">Hostel Allocations</h6></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Student</th><th>Hostel/Room</th><th>Status</th><th>Action</th></tr></thead><tbody>
            @forelse($allocations as $allocation)
                <tr>
                    <td>{{ $allocation->student?->full_name }}</td><td>{{ $allocation->hostel?->name }} / {{ $allocation->room?->room_number }}</td><td>{{ strtoupper($allocation->status) }}</td>
                    <td>
                        @if($allocation->status === 'active')
                            <form method="POST" action="{{ route('tenant.services.hostel-allocations.vacate', ['school_slug' => $school->slug, 'allocation' => $allocation->id]) }}">@csrf<button class="btn btn-sm btn-outline-danger" type="submit">Vacate</button></form>
                        @else
                            <span class="text-muted">Completed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-3">No allocations yet.</td></tr>
            @endforelse
        </tbody></table></div><div class="card-footer">{{ $allocations->appends(request()->query())->links() }}</div></div>
    </div>
</div>
@endsection
