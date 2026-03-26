<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alumni extends Model
{
    use HasFactory, BelongsToSchool;

    protected $table = 'alumni';

    protected $fillable = [
        'school_id',
        'student_id',
        'course_id',
        'graduation_year',
        'current_company',
        'current_designation',
        'linkedin_url',
        'achievements',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
