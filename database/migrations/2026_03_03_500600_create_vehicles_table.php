<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number');
            $table->enum('type', ['bus', 'van', 'car'])->default('bus')->index();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->foreignId('driver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->index();
            $table->timestamps();

            $table->unique(['school_id', 'registration_number']);
            $table->index(['school_id', 'driver_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
