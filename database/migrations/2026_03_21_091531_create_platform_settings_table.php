<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default platform settings
        DB::table('platform_settings')->insert([
            [
                'key' => 'trial_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default trial period (in days) for new schools',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'trial_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Whether new schools get a trial period',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
