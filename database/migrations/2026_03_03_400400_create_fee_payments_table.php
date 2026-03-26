<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_assignment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date')->index();
            $table->enum('payment_method', ['cash', 'bank', 'card', 'online', 'cheque'])->default('cash')->index();
            $table->string('transaction_reference')->nullable();
            $table->string('receipt_number');
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'payment_date']);
            $table->unique(['school_id', 'receipt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
