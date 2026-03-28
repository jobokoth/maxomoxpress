<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'logo',
        'cover_image',
        'website',
        'established_year',
        'timezone',
        'locale',
        'currency',
        'academic_year_start_month',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
        'settings',
        'onboarding_step',
        'onboarding_completed_at',
        'trial_ends_at',
        'is_trial',
    ];

    protected function casts(): array
    {
        return [
            'subscription_expires_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function isInTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if ($this->trial_ends_at === null) {
            return 0;
        }

        $days = (int) now()->diffInDays($this->trial_ends_at, false);

        return max(0, $days);
    }

    protected static function booted(): void
    {
        static::creating(function (School $school): void {
            if (blank($school->ulid)) {
                $school->ulid = (string) Str::ulid();
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role_in_school', 'is_primary_school', 'joined_at', 'left_at'])
            ->withTimestamps()
            ->withCasts(['joined_at' => 'datetime', 'left_at' => 'datetime']);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function platformSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PlatformSubscription::class)->latestOfMany();
    }

    public function platformSubscriptions(): HasMany
    {
        return $this->hasMany(PlatformSubscription::class);
    }

    public function quickBooksConnection(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(QuickBooksConnection::class)->whereNull('disconnected_at');
    }

    /** Compute current billing tier based on active student count. */
    public function currentBillingTier(): array
    {
        $count = $this->students()->count();

        return PlatformSubscription::calculatePricing($count);
    }
}
