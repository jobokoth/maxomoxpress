<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_assignment_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'receipt_number',
        'collected_by_user_id',
        'notes',
        // Mpesa
        'mpesa_receipt_no',
        'mpesa_phone',
        'mpesa_transaction_id',
        // Bank transfer
        'bank_transfer_type',
        'bank_transfer_ref',
        // Cheque
        'cheque_number',
        'cheque_bank',
        'cheque_date',
        // Verification
        'verified_at',
        'verified_by_user_id',
        // QuickBooks
        'qb_sales_receipt_id',
        'qb_doc_number',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
            'cheque_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FeeAssignment::class, 'fee_assignment_id');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }
}
