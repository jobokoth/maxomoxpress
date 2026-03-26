<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'registration_number',
        'type',
        'make',
        'model',
        'capacity',
        'driver_user_id',
        'status',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentTransport::class);
    }
}
