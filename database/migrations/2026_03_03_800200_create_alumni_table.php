<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('graduation_year')->index();
            $table->string('current_company')->nullable();
            $table->string('current_designation')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('achievements')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'graduation_year']);
            $table->unique(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
