<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaCredential extends Model
{
    protected $fillable = [
        'school_id',
        'shortcode',
        'shortcode_type',
        'consumer_key',
        'consumer_secret',
        'passkey',
        'environment',
        'initiator_name',
        'initiator_security_credential',
    ];

    /** Encrypted columns — Laravel automatically encrypts/decrypts via cast. */
    protected function casts(): array
    {
        return [
            'consumer_key' => 'encrypted',
            'consumer_secret' => 'encrypted',
            'passkey' => 'encrypted',
            'initiator_security_credential' => 'encrypted',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function baseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }
}
