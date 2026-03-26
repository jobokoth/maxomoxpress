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
        Schema::create('mpesa_credentials', function (Blueprint $table) {
            $table->id();
            // null school_id = platform-level credentials (MasomoPlus own Daraja account)
            $table->foreignId('school_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('shortcode', 20);                    // paybill or till number
            $table->enum('shortcode_type', ['paybill', 'till'])->default('paybill');
            $table->text('consumer_key');                       // encrypted
            $table->text('consumer_secret');                    // encrypted
            $table->text('passkey')->nullable();                // encrypted — for STK push
            $table->enum('environment', ['sandbox', 'production'])->default('production');
            $table->string('initiator_name')->nullable();       // for B2C/reversal
            $table->text('initiator_security_credential')->nullable(); // encrypted
            $table->timestamps();
            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_credentials');
    }
};
