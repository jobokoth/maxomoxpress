<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'ulid',
        'email',
        'password',
        'phone',
        'avatar',
        'gender',
        'date_of_birth',
        'address',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->ulid)) {
                $user->ulid = (string) Str::ulid();
            }
        });
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['role_in_school', 'is_primary_school', 'joined_at', 'left_at'])
            ->withTimestamps()
            ->withCasts(['joined_at' => 'datetime', 'left_at' => 'datetime']);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(CourseSubjectAssignment::class, 'teacher_user_id');
    }

    public function studentAttendancesMarked(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'marked_by_user_id');
    }

    public function staffAttendancesMarked(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'marked_by_user_id');
    }

    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'teacher_user_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by_user_id');
    }

    public function disciplineIncidents(): HasMany
    {
        return $this->hasMany(DisciplineIncident::class, 'reported_by_user_id');
    }

    public function clinicRecords(): HasMany
    {
        return $this->hasMany(ClinicRecord::class, 'recorded_by_user_id');
    }

    public function issuedBooks(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'issued_by_user_id');
    }

    public function returnedBooks(): HasMany
    {
        return $this->hasMany(BookIssue::class, 'returned_to_user_id');
    }

    public function drivenVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'driver_user_id');
    }

    public function wardenedHostels(): HasMany
    {
        return $this->hasMany(Hostel::class, 'warden_user_id');
    }

    public function guardianProfiles(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
