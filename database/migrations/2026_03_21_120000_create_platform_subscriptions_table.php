<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('trial')->index();
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');

            // Pricing tier snapshot (so historical invoices are accurate even if tiers change)
            $table->unsignedInteger('student_count_at_billing')->default(0);
            $table->unsignedSmallInteger('tier')->default(1)->comment('1=0-100, 2=101-400, 3=401+');
            $table->unsignedInteger('amount_kes')->default(0)->comment('Amount in KES');

            // PayStack
            $table->string('paystack_customer_code')->nullable()->index();
            $table->string('paystack_subscription_code')->nullable()->unique()->nullable();
            $table->string('paystack_authorization_code')->nullable();
            $table->string('paystack_email_token')->nullable();

            // Billing dates
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->unsignedInteger('last_payment_amount_kes')->nullable();

            // Notes (for support team)
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_subscriptions');
    }
};
