<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('website')->nullable();
            $table->unsignedSmallInteger('established_year')->nullable();
            $table->string('timezone')->default('Africa/Nairobi');
            $table->string('locale')->default('en');
            $table->string('currency', 12)->default('KES');
            $table->unsignedTinyInteger('academic_year_start_month')->default(1);
            $table->string('subscription_plan')->default('starter');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

