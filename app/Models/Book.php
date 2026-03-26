<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'title',
        'author',
        'isbn',
        'copies_total',
        'copies_available',
        'location_rack',
        'status',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }
}
