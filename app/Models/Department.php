<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'head_user_id',
        'description',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}

