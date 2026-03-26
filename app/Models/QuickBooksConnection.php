<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickBooksConnection extends Model
{
    protected $table = 'quickbooks_connections';

    protected $fillable = [
        'school_id',
        'access_token',
        'refresh_token',
        'realm_id',
        'token_expires_at',
        'company_name',
        'environment',
        'connected_at',
        'last_refreshed_at',
        'disconnected_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_refreshed_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isConnected(): bool
    {
        return $this->disconnected_at === null;
    }

    public function isTokenExpired(): bool
    {
        // Treat as expired 5 minutes early to avoid edge cases
        return $this->token_expires_at->subMinutes(5)->isPast();
    }

    public function baseUrl(): string
    {
        return $this->environment === 'sandbox'
            ? 'https://sandbox-quickbooks.api.intuit.com/v3/company/'
            : 'https://quickbooks.api.intuit.com/v3/company/';
    }
}
