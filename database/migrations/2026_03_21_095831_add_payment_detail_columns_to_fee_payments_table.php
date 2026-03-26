<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend payment_method enum to include mpesa and bank_transfer
        DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_method ENUM('cash','bank','bank_transfer','card','online','cheque','mpesa') NOT NULL DEFAULT 'cash'");

        Schema::table('fee_payments', function (Blueprint $table) {
            // Mpesa (may already exist from a partial prior run — guard with hasColumn)
            if (! Schema::hasColumn('fee_payments', 'mpesa_receipt_no')) {
                $table->string('mpesa_receipt_no', 30)->nullable()->after('transaction_reference')->index();
            }
            if (! Schema::hasColumn('fee_payments', 'mpesa_phone')) {
                $table->string('mpesa_phone', 20)->nullable()->after('mpesa_receipt_no');
            }
            if (! Schema::hasColumn('fee_payments', 'mpesa_transaction_id')) {
                $table->char('mpesa_transaction_id', 26)->nullable()->after('mpesa_phone')->index();
            }

            // Bank transfer
            $table->enum('bank_transfer_type', ['rtgs', 'swift', 'pesalink'])->nullable()->after('mpesa_transaction_id');
            $table->string('bank_transfer_ref', 100)->nullable()->after('bank_transfer_type');

            // Cheque
            $table->string('cheque_number', 50)->nullable()->after('bank_transfer_ref');
            $table->string('cheque_bank', 100)->nullable()->after('cheque_number');
            $table->date('cheque_date')->nullable()->after('cheque_bank');

            // Verification
            $table->timestamp('verified_at')->nullable()->after('cheque_date');
            $table->foreignId('verified_by_user_id')->nullable()->after('verified_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex(['mpesa_transaction_id']);
            $table->dropColumn([
                'mpesa_receipt_no', 'mpesa_phone', 'mpesa_transaction_id',
                'bank_transfer_type', 'bank_transfer_ref',
                'cheque_number', 'cheque_bank', 'cheque_date',
                'verified_at', 'verified_by_user_id',
            ]);
        });

        DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_method ENUM('cash','bank','card','online','cheque') NOT NULL DEFAULT 'cash'");
    }
};
