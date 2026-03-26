<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issued_date')->index();
            $table->date('due_date')->index();
            $table->date('returned_date')->nullable()->index();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
