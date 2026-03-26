<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('incident_date')->index();
            $table->string('incident_type');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low')->index();
            $table->enum('status', ['open', 'under_review', 'resolved', 'closed'])->default('open')->index();
            $table->text('action_taken')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_incidents');
    }
};
