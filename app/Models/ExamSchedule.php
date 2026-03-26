<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'exam_id',
        'course_id',
        'batch_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'pass_marks',
        'invigilator_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invigilator_user_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }
}
