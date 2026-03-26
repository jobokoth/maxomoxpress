<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->enum('status', ['active', 'vacated'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'hostel_room_id', 'status']);
            $table->unique(['student_id', 'status'], 'student_hostel_status_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
    }
};
