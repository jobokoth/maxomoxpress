<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('exam_type', ['quiz', 'cat', 'midterm', 'endterm', 'practical', 'mock', 'final'])->default('cat')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
