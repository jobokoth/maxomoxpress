<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    use BelongsToSchool, HasUlids;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_assignment_id',
        'fee_payment_id',
        'type',
        'status',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'transaction_id',
        'amount',
        'phone_number',
        'bill_ref_number',
        'paybill_shortcode',
        'transaction_time',
        'callback_received_at',
        'raw_payload',
        'result_code',
        'result_description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_time' => 'datetime',
            'callback_received_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeAssignment(): BelongsTo
    {
        return $this->belongsTo(FeeAssignment::class);
    }

    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
