<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks_obtained', 6, 2)->default(0);
            $table->string('grade_letter', 8)->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('entered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_schedule_id', 'student_id'], 'schedule_student_unique');
            $table->index(['school_id', 'exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
