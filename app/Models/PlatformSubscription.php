<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'status',
        'billing_cycle',
        'student_count_at_billing',
        'tier',
        'amount_kes',
        'paystack_customer_code',
        'paystack_subscription_code',
        'paystack_authorization_code',
        'paystack_email_token',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'last_payment_at',
        'last_payment_amount_kes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'next_billing_date' => 'datetime',
            'last_payment_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PlatformInvoice::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['expired', 'cancelled']);
    }

    public function tierLabel(): string
    {
        return match ($this->tier) {
            1 => 'Starter (≤100 students)',
            2 => 'Growth (101–400 students)',
            3 => 'Enterprise (401+ students)',
            default => 'Unknown',
        };
    }

    // ─── Pricing Engine ───────────────────────────────────────────────────────

    /**
     * Calculate the tier and monthly amount for a given student count.
     *
     * @return array{tier: int, amount_kes: int}
     */
    public static function calculatePricing(int $studentCount): array
    {
        // Tiers sourced from PlatformSetting so super admin can adjust them
        $t1Max = (int) PlatformSetting::get('tier1_max_students', 100);
        $t2Max = (int) PlatformSetting::get('tier2_max_students', 400);

        $t1Price = (int) PlatformSetting::get('tier1_price_kes', 1400);
        $t2Price = (int) PlatformSetting::get('tier2_price_kes', 3400);
        $t3Price = (int) PlatformSetting::get('tier3_price_kes', 5400);

        if ($studentCount <= $t1Max) {
            return ['tier' => 1, 'amount_kes' => $t1Price];
        }

        if ($studentCount <= $t2Max) {
            return ['tier' => 2, 'amount_kes' => $t2Price];
        }

        return ['tier' => 3, 'amount_kes' => $t3Price];
    }
}
