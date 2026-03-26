<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quickbooks_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            $table->string('entity_type', 50)->comment('student, fee_payment, fee_structure');
            $table->unsignedBigInteger('entity_id');
            $table->string('qb_entity_type', 50)->comment('Customer, SalesReceipt, Item');
            $table->string('qb_id')->nullable()->comment('QuickBooks entity ID');
            $table->string('qb_doc_number')->nullable()->comment('QB transaction number');

            $table->enum('action', ['create', 'update', 'void', 'delete'])->default('create');
            $table->enum('status', ['success', 'failed', 'skipped'])->default('success')->index();

            $table->text('error_message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quickbooks_sync_logs');
    }
};
