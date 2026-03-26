<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_event_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['email', 'sms', 'whatsapp'])->index();
            $table->enum('audience', ['parents', 'students', 'staff', 'all'])->default('all')->index();
            $table->integer('offset_minutes')->default(60);
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->enum('status', ['scheduled', 'sent', 'failed'])->default('scheduled')->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reminders');
    }
};
