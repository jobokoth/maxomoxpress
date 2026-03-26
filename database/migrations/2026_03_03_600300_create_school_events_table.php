<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['academic', 'sports', 'cultural', 'holiday', 'meeting', 'exam', 'other'])->default('other')->index();
            $table->datetime('start_at')->index();
            $table->datetime('end_at')->nullable()->index();
            $table->string('location')->nullable();
            $table->enum('audience', ['all', 'parents', 'students', 'staff'])->default('all')->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_events');
    }
};
