<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quickbooks_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();

            // OAuth tokens (encrypted at rest via model cast)
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('realm_id', 50)->index()->comment('QB Company ID');
            $table->timestamp('token_expires_at');

            // Connection metadata
            $table->string('company_name')->nullable();
            $table->enum('environment', ['production', 'sandbox'])->default('production');
            $table->timestamp('connected_at');
            $table->timestamp('last_refreshed_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quickbooks_connections');
    }
};
