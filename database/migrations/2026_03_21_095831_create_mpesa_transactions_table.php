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
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_payment_id')->nullable()->constrained()->nullOnDelete();

            // Transaction type
            $table->enum('type', ['stk_push', 'c2b'])->default('c2b')->index();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending')->index();

            // Daraja identifiers
            $table->string('merchant_request_id')->nullable()->index(); // STK push
            $table->string('checkout_request_id')->nullable()->index(); // STK push
            $table->string('mpesa_receipt_number')->nullable()->index(); // e.g. OEI2AK4Q16
            $table->string('transaction_id')->nullable();               // C2B TransID

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('phone_number', 20)->nullable();             // MSISDN
            $table->string('bill_ref_number', 100)->nullable();         // account ref from payer
            $table->string('paybill_shortcode', 20)->nullable();

            // Timestamps from Safaricom
            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('callback_received_at')->nullable();

            // Raw Safaricom payload (for audit)
            $table->json('raw_payload')->nullable();
            $table->string('result_code')->nullable();
            $table->string('result_description')->nullable();

            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'mpesa_receipt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
