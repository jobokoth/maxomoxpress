<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'min_mark',
        'max_mark',
        'grade_letter',
        'grade_point',
        'remarks',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'min_mark' => 'decimal:2',
            'max_mark' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }
}
