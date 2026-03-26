<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('school_id')->constrained('users')->nullOnDelete();
            $table->unique(['school_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
