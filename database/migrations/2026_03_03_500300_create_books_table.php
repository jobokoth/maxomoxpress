<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->nullable();
            $table->unsignedInteger('copies_total')->default(1);
            $table->unsignedInteger('copies_available')->default(1);
            $table->string('location_rack')->nullable();
            $table->enum('status', ['available', 'limited', 'unavailable'])->default('available')->index();
            $table->timestamps();

            $table->index(['school_id', 'title']);
            $table->index(['school_id', 'author']);
            $table->unique(['school_id', 'isbn']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
