<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('room_number');
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory'])->default('single')->index();
            $table->unsignedInteger('capacity')->default(1);
            $table->unsignedInteger('occupied_beds')->default(0);
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available')->index();
            $table->timestamps();

            $table->unique(['hostel_id', 'room_number']);
            $table->index(['school_id', 'hostel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
