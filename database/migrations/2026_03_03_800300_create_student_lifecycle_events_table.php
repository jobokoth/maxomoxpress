<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_lifecycle_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 40)->index();
            $table->json('payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('event_date')->useCurrent();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_lifecycle_events');
    }
};
