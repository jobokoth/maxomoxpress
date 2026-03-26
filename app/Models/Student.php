<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'course_id',
        'batch_id',
        'student_id_number',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'admission_date',
        'enrollment_date',
        'admission_status',
        'lifecycle_status',
        'promoted_at',
        'repeated_at',
        'transferred_at',
        'graduated_at',
        'exited_at',
        'transfer_destination',
        'exit_reason',
        'exit_notes',
        'clearance_completed_at',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'city',
        'country',
        'blood_group',
        'medical_conditions',
        'allergies',
        'medical_notes',
        'previous_school_name',
        'previous_school_address',
        'previous_school_notes',
        'meta',
        'user_id',
        'qb_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'enrollment_date' => 'date',
            'promoted_at' => 'date',
            'repeated_at' => 'date',
            'transferred_at' => 'date',
            'graduated_at' => 'date',
            'exited_at' => 'date',
            'clearance_completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            $school = app()->bound('current_school') ? app('current_school') : null;
            $year = now()->format('Y');

            if (blank($student->student_id_number) && $school) {
                $sequence = static::query()->withoutGlobalScopes()->where('school_id', $school->id)->count() + 1;
                $student->student_id_number = sprintf('STD-%s-%04d', $year, $sequence);
            }

            if (blank($student->admission_number) && $school) {
                $sequence = static::query()->withoutGlobalScopes()->where('school_id', $school->id)->count() + 1;
                $student->admission_number = sprintf('ADM-%s-%04d', $year, $sequence);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot(['relationship', 'is_primary_contact', 'notes'])
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function feeAssignments(): HasMany
    {
        return $this->hasMany(FeeAssignment::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function disciplineIncidents(): HasMany
    {
        return $this->hasMany(DisciplineIncident::class);
    }

    public function clinicRecords(): HasMany
    {
        return $this->hasMany(ClinicRecord::class);
    }

    public function bookIssues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }

    public function transportAssignments(): HasMany
    {
        return $this->hasMany(StudentTransport::class);
    }

    public function hostelAllocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(StudentLifecycleEvent::class);
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(StudentClearance::class);
    }

    public function alumniProfile(): HasOne
    {
        return $this->hasOne(Alumni::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
}
