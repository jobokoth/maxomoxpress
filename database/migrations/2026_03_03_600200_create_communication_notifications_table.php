<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('channel', ['email', 'sms', 'whatsapp'])->index();
            $table->enum('audience', ['parents', 'students', 'staff', 'custom'])->default('parents')->index();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_contact')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('related_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_notifications');
    }
};
