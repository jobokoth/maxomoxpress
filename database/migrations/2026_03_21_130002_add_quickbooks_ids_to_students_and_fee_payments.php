<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('qb_customer_id')->nullable()->after('user_id')->index()
                ->comment('QuickBooks Customer ID');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('qb_sales_receipt_id')->nullable()->after('verified_by_user_id')->index()
                ->comment('QuickBooks SalesReceipt ID');
            $table->string('qb_doc_number')->nullable()->after('qb_sales_receipt_id')
                ->comment('QB transaction doc number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('qb_customer_id');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['qb_sales_receipt_id', 'qb_doc_number']);
        });
    }
};
