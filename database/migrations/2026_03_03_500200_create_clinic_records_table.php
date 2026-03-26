<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('visit_date')->index();
            $table->string('complaint');
            $table->string('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->string('medication')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('status', ['open', 'in_treatment', 'recovered', 'referred'])->default('open')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_records');
    }
};
