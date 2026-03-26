<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolPaymentConfig extends Model
{
    protected $fillable = [
        'school_id',
        'mpesa_mode',
        'mpesa_shortcode',
        'mpesa_account_reference',
        'mpesa_urls_registered',
        'mpesa_urls_registered_at',
        'bank_transfer_enabled',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'bank_swift_code',
        'accepts_rtgs',
        'accepts_swift',
        'accepts_pesalink',
        'cheques_enabled',
        'cheques_payable_to',
        'cash_enabled',
    ];

    protected function casts(): array
    {
        return [
            'mpesa_urls_registered' => 'boolean',
            'mpesa_urls_registered_at' => 'datetime',
            'bank_transfer_enabled' => 'boolean',
            'accepts_rtgs' => 'boolean',
            'accepts_swift' => 'boolean',
            'accepts_pesalink' => 'boolean',
            'cheques_enabled' => 'boolean',
            'cash_enabled' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isMpesaEnabled(): bool
    {
        return $this->mpesa_mode !== 'disabled';
    }

    /** Returns the accepted bank transfer types as an array. */
    public function acceptedTransferTypes(): array
    {
        $types = [];
        if ($this->accepts_rtgs) {
            $types[] = 'rtgs';
        }
        if ($this->accepts_swift) {
            $types[] = 'swift';
        }
        if ($this->accepts_pesalink) {
            $types[] = 'pesalink';
        }

        return $types;
    }
}
