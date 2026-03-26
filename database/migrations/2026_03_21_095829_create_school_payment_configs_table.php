<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_payment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();

            // Mpesa
            $table->enum('mpesa_mode', ['disabled', 'own_daraja', 'bank_paybill', 'platform'])->default('disabled')->index();
            $table->string('mpesa_shortcode', 20)->nullable();
            $table->string('mpesa_account_reference', 100)->nullable();
            $table->boolean('mpesa_urls_registered')->default(false);
            $table->timestamp('mpesa_urls_registered_at')->nullable();

            // Bank transfer
            $table->boolean('bank_transfer_enabled')->default(false);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 150)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('bank_swift_code', 20)->nullable();
            $table->boolean('accepts_rtgs')->default(true);
            $table->boolean('accepts_swift')->default(false);
            $table->boolean('accepts_pesalink')->default(true);

            // Cheque
            $table->boolean('cheques_enabled')->default(false);
            $table->string('cheques_payable_to', 150)->nullable();

            // Cash
            $table->boolean('cash_enabled')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_payment_configs');
    }
};
