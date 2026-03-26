<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickBooksSyncLog extends Model
{
    protected $table = 'quickbooks_sync_logs';

    protected $fillable = [
        'school_id',
        'entity_type',
        'entity_id',
        'qb_entity_type',
        'qb_id',
        'qb_doc_number',
        'action',
        'status',
        'error_message',
        'request_payload',
        'response_payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
