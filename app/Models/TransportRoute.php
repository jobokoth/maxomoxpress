<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'distance_km',
        'departure_time',
        'arrival_time',
        'fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'fee' => 'decimal:2',
        ];
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentTransport::class);
    }
}
