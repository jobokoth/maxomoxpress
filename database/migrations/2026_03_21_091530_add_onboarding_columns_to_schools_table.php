<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_step')->default(0)->after('is_active');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
            $table->timestamp('trial_ends_at')->nullable()->after('onboarding_completed_at');
            $table->boolean('is_trial')->default(true)->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['onboarding_step', 'onboarding_completed_at', 'trial_ends_at', 'is_trial']);
        });
    }
};
