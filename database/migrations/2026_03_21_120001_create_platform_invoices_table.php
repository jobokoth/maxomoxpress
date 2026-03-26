<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_subscription_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->enum('status', ['draft', 'sent', 'paid', 'failed', 'void'])->default('draft')->index();

            $table->unsignedInteger('amount_kes');
            $table->unsignedSmallInteger('tier');
            $table->unsignedInteger('student_count');

            // PayStack reference
            $table->string('paystack_reference')->nullable()->unique()->nullable();
            $table->string('paystack_transaction_id')->nullable();
            $table->json('paystack_payload')->nullable();

            $table->timestamp('issued_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_invoices');
    }
};
