<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\ClinicRecord;
use App\Models\DisciplineIncident;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentServicesController extends Controller
{
    public function index(): View
    {
        $school = app('current_school');

        return view('services.index', [
            'school' => $school,
            'students' => Student::query()->orderBy('last_name')->orderBy('first_name')->get(),
            'years' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'staffUsers' => User::query()->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))->orderBy('name')->get(),
            'incidents' => DisciplineIncident::query()->with(['student', 'reportedBy'])->latest('incident_date')->paginate(12, ['*'], 'incidents_page'),
            'clinicRecords' => ClinicRecord::query()->with(['student', 'recordedBy'])->latest('visit_date')->paginate(12, ['*'], 'clinic_page'),
            'books' => Book::query()->latest()->paginate(12, ['*'], 'books_page'),
            'bookIssues' => BookIssue::query()->with(['book', 'student', 'issuedBy'])->latest('issued_date')->paginate(12, ['*'], 'issues_page'),
            'routes' => TransportRoute::query()->latest()->paginate(12, ['*'], 'routes_page'),
            'vehicles' => Vehicle::query()->with('driver')->latest()->paginate(12, ['*'], 'vehicles_page'),
            'studentTransports' => StudentTransport::query()->with(['student', 'route', 'vehicle'])->latest()->paginate(12, ['*'], 'transport_page'),
            'hostels' => Hostel::query()->with('warden')->latest()->paginate(10, ['*'], 'hostels_page'),
            'rooms' => HostelRoom::query()->with('hostel')->latest()->paginate(12, ['*'], 'rooms_page'),
            'allocations' => HostelAllocation::query()->with(['student', 'hostel', 'room'])->latest()->paginate(12, ['*'], 'allocations_page'),
        ]);
    }

    public function storeIncident(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', app('current_school')->id)],
            'incident_date' => ['required', 'date'],
            'incident_type' => ['required', 'string', 'max:255'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['nullable', Rule::in(['open', 'under_review', 'resolved', 'closed'])],
            'action_taken' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        DisciplineIncident::query()->create($validated + ['reported_by_user_id' => $request->user()?->id]);

        return back()->with('status', 'Discipline incident recorded.');
    }

    public function storeClinicRecord(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', app('current_school')->id)],
            'visit_date' => ['required', 'date'],
            'complaint' => ['required', 'string', 'max:255'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'treatment' => ['nullable', 'string'],
            'medication' => ['nullable', 'string', 'max:255'],
            'follow_up_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['open', 'in_treatment', 'recovered', 'referred'])],
            'notes' => ['nullable', 'string'],
        ]);

        ClinicRecord::query()->create($validated + ['recorded_by_user_id' => $request->user()?->id]);

        return back()->with('status', 'Clinic record added.');
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:255'],
            'copies_total' => ['required', 'integer', 'min:1', 'max:10000'],
            'location_rack' => ['nullable', 'string', 'max:255'],
        ]);

        Book::query()->create([
            ...$validated,
            'copies_available' => $validated['copies_total'],
            'status' => 'available',
        ]);

        return back()->with('status', 'Book added to library.');
    }

    public function issueBook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', Rule::exists('books', 'id')->where('school_id', app('current_school')->id)],
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', app('current_school')->id)],
            'issued_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issued_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = Book::query()->findOrFail($validated['book_id']);

        if ($book->copies_available <= 0) {
            throw ValidationException::withMessages(['book_id' => 'No available copies for this book.']);
        }

        DB::transaction(function () use ($validated, $book, $request): void {
            BookIssue::query()->create($validated + [
                'issued_by_user_id' => $request->user()?->id,
                'status' => 'issued',
            ]);

            $book->decrement('copies_available');
            $book->update(['status' => $book->copies_available > 0 ? 'available' : 'unavailable']);
        });

        return back()->with('status', 'Book issued successfully.');
    }

    public function returnBook(Request $request, BookIssue $issue): RedirectResponse
    {
        $validated = $request->validate([
            'returned_date' => ['required', 'date'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($issue->status === 'returned') {
            return back()->with('status', 'Book already returned.');
        }

        DB::transaction(function () use ($issue, $validated, $request): void {
            $issue->update([
                'returned_date' => $validated['returned_date'],
                'fine_amount' => (float) ($validated['fine_amount'] ?? 0),
                'notes' => $validated['notes'] ?? $issue->notes,
                'returned_to_user_id' => $request->user()?->id,
                'status' => 'returned',
            ]);

            $book = $issue->book;
            if ($book) {
                $book->increment('copies_available');
                $book->update(['status' => $book->copies_available > 0 ? 'available' : 'unavailable']);
            }
        });

        return back()->with('status', 'Book return recorded.');
    }

    public function storeTransportRoute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        TransportRoute::query()->create($validated + ['fee' => $validated['fee'] ?? 0]);

        return back()->with('status', 'Transport route created.');
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'registration_number' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['bus', 'van', 'car'])],
            'make' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'driver_user_id' => ['nullable', Rule::exists('users', 'id')],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'maintenance'])],
        ]);

        $this->validateSchoolUser($validated['driver_user_id'] ?? null, $schoolId);

        Vehicle::query()->create($validated);

        return back()->with('status', 'Vehicle added.');
    }

    public function assignTransport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', app('current_school')->id)],
            'transport_route_id' => ['required', Rule::exists('transport_routes', 'id')->where('school_id', app('current_school')->id)],
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('school_id', app('current_school')->id)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'pickup_point' => ['nullable', 'string', 'max:255'],
            'dropoff_point' => ['nullable', 'string', 'max:255'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        StudentTransport::query()->updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'transport_route_id' => $validated['transport_route_id'],
                'status' => $validated['status'] ?? 'active',
            ],
            $validated + ['fee_amount' => $validated['fee_amount'] ?? 0]
        );

        return back()->with('status', 'Student transport assignment saved.');
    }

    public function storeHostel(Request $request): RedirectResponse
    {
        $schoolId = app('current_school')->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['boys', 'girls', 'mixed'])],
            'warden_user_id' => ['nullable', Rule::exists('users', 'id')],
            'capacity' => ['required', 'integer', 'min:0', 'max:10000'],
            'facilities' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $this->validateSchoolUser($validated['warden_user_id'] ?? null, $schoolId);

        Hostel::query()->create($validated);

        return back()->with('status', 'Hostel created.');
    }

    public function storeHostelRoom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hostel_id' => ['required', Rule::exists('hostels', 'id')->where('school_id', app('current_school')->id)],
            'room_number' => ['required', 'string', 'max:255'],
            'room_type' => ['required', Rule::in(['single', 'double', 'triple', 'dormitory'])],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['available', 'occupied', 'maintenance'])],
        ]);

        HostelRoom::query()->create($validated + ['monthly_fee' => $validated['monthly_fee'] ?? 0]);

        return back()->with('status', 'Hostel room added.');
    }

    public function allocateHostel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', app('current_school')->id)],
            'hostel_id' => ['required', Rule::exists('hostels', 'id')->where('school_id', app('current_school')->id)],
            'hostel_room_id' => ['required', Rule::exists('hostel_rooms', 'id')->where('school_id', app('current_school')->id)],
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', app('current_school')->id)],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $room = HostelRoom::query()->findOrFail($validated['hostel_room_id']);

        if ($room->occupied_beds >= $room->capacity) {
            throw ValidationException::withMessages([
                'hostel_room_id' => 'Selected room is full.',
            ]);
        }

        DB::transaction(function () use ($validated, $room): void {
            $existing = HostelAllocation::query()
                ->where('student_id', $validated['student_id'])
                ->where('status', 'active')
                ->first();

            if ($existing && (int) $existing->hostel_room_id !== (int) $validated['hostel_room_id']) {
                $oldRoom = $existing->room;
                if ($oldRoom) {
                    $oldRoom->update([
                        'occupied_beds' => max((int) $oldRoom->occupied_beds - 1, 0),
                    ]);
                    $oldRoom->refresh();
                    $oldRoom->update(['status' => $oldRoom->occupied_beds > 0 ? 'occupied' : 'available']);
                }
            }

            HostelAllocation::query()->updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'status' => 'active',
                ],
                $validated + ['status' => 'active']
            );

            if (! $existing || (int) $existing->hostel_room_id !== (int) $validated['hostel_room_id']) {
                $room->increment('occupied_beds');
                $room->refresh();
                $room->update(['status' => $room->occupied_beds >= $room->capacity ? 'occupied' : 'available']);
            }
        });

        return back()->with('status', 'Hostel allocated successfully.');
    }

    public function vacateHostelAllocation(HostelAllocation $allocation): RedirectResponse
    {
        if ($allocation->status === 'vacated') {
            return back()->with('status', 'Allocation already vacated.');
        }

        DB::transaction(function () use ($allocation): void {
            $allocation->update([
                'status' => 'vacated',
                'to_date' => $allocation->to_date ?: now()->toDateString(),
            ]);

            $room = $allocation->room;
            if ($room) {
                $room->update([
                    'occupied_beds' => max((int) $room->occupied_beds - 1, 0),
                ]);

                $room->refresh();
                $room->update(['status' => $room->occupied_beds > 0 ? 'occupied' : 'available']);
            }
        });

        return back()->with('status', 'Hostel allocation vacated.');
    }

    private function validateSchoolUser(?int $userId, int $schoolId): void
    {
        if (! $userId) {
            return;
        }

        $belongs = User::query()
            ->where('id', $userId)
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $schoolId))
            ->exists();

        abort_unless($belongs, 422, 'Selected user does not belong to this school.');
    }
}
