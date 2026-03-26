<?php

namespace App\Jobs;

use App\Models\QuickBooksSyncLog;
use App\Models\Student;
use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncStudentToQuickBooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly Student $student) {}

    public function handle(): void
    {
        $school = $this->student->school;

        // Bail if school has no QB connection
        $conn = $school->quickBooksConnection;
        if (! $conn) {
            return;
        }

        $service = QuickBooksService::forSchool($school);
        $action = $this->student->qb_customer_id ? 'update' : 'create';
        $payload = $service->buildCustomerPayload($this->student);

        try {
            $qbId = $service->upsertCustomer($payload);

            if (! $qbId) {
                throw new \RuntimeException('QB returned no Customer ID');
            }

            $this->student->updateQuietly(['qb_customer_id' => $qbId]);

            QuickBooksSyncLog::create([
                'school_id' => $school->id,
                'entity_type' => 'student',
                'entity_id' => $this->student->id,
                'qb_entity_type' => 'Customer',
                'qb_id' => $qbId,
                'action' => $action,
                'status' => 'success',
                'request_payload' => $payload,
                'synced_at' => now(),
            ]);

            Log::info("QB: Student {$this->student->id} synced as Customer {$qbId}");
        } catch (Throwable $e) {
            QuickBooksSyncLog::create([
                'school_id' => $school->id,
                'entity_type' => 'student',
                'entity_id' => $this->student->id,
                'qb_entity_type' => 'Customer',
                'action' => $action,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'request_payload' => $payload,
                'synced_at' => now(),
            ]);

            throw $e; // allow queue retry
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error("QB sync failed for student {$this->student->id}", ['error' => $e->getMessage()]);
    }
}
