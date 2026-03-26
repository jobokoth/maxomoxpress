<?php

namespace App\Jobs;

use App\Models\FeePayment;
use App\Models\School;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QuickBooksBulkSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public readonly School $school,
        public readonly string $mode = 'all'  // 'all', 'students', 'payments'
    ) {}

    public function handle(): void
    {
        Log::info("QB bulk sync started for school {$this->school->slug}, mode: {$this->mode}");

        if (in_array($this->mode, ['all', 'students'])) {
            Student::withoutGlobalScopes()
                ->where('school_id', $this->school->id)
                ->whereIn('admission_status', ['active', 'enrolled'])
                ->chunkById(50, function ($students): void {
                    foreach ($students as $student) {
                        SyncStudentToQuickBooks::dispatch($student)->onQueue('quickbooks');
                    }
                });
        }

        if (in_array($this->mode, ['all', 'payments'])) {
            FeePayment::withoutGlobalScopes()
                ->where('school_id', $this->school->id)
                ->whereNull('qb_sales_receipt_id')
                ->chunkById(50, function ($payments): void {
                    foreach ($payments as $payment) {
                        SyncPaymentToQuickBooks::dispatch($payment)->onQueue('quickbooks');
                    }
                });
        }

        Log::info("QB bulk sync dispatched for school {$this->school->slug}");
    }
}
