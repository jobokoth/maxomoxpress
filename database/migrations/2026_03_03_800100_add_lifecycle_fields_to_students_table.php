<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->enum('lifecycle_status', ['in_progress', 'active', 'repeating', 'transferred', 'graduated', 'exited'])
                ->default('in_progress')
                ->after('admission_status')
                ->index();
            $table->date('promoted_at')->nullable()->after('lifecycle_status');
            $table->date('repeated_at')->nullable()->after('promoted_at');
            $table->date('transferred_at')->nullable()->after('repeated_at');
            $table->date('graduated_at')->nullable()->after('transferred_at');
            $table->date('exited_at')->nullable()->after('graduated_at');
            $table->string('transfer_destination')->nullable()->after('exited_at');
            $table->enum('exit_reason', ['graduated', 'transferred', 'dropout', 'expelled', 'deceased', 'other'])->nullable()->after('transfer_destination');
            $table->text('exit_notes')->nullable()->after('exit_reason');
            $table->timestamp('clearance_completed_at')->nullable()->after('exit_notes');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn([
                'lifecycle_status',
                'promoted_at',
                'repeated_at',
                'transferred_at',
                'graduated_at',
                'exited_at',
                'transfer_destination',
                'exit_reason',
                'exit_notes',
                'clearance_completed_at',
            ]);
        });
    }
};
