<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformInvoice extends Model
{
    protected $fillable = [
        'school_id',
        'platform_subscription_id',
        'invoice_number',
        'status',
        'amount_kes',
        'tier',
        'student_count',
        'paystack_reference',
        'paystack_transaction_id',
        'paystack_payload',
        'issued_at',
        'paid_at',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'paystack_payload' => 'array',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id');
    }

    public static function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd');
        $last = static::where('invoice_number', 'like', $prefix.'-%')
            ->latest('id')
            ->value('invoice_number');

        $next = $last ? ((int) last(explode('-', $last))) + 1 : 1;

        return sprintf('%s-%04d', $prefix, $next);
    }
}
