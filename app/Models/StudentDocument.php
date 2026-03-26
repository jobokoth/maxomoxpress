<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
