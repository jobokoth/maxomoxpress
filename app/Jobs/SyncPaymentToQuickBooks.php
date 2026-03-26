<?php

namespace App\Jobs;

use App\Models\FeePayment;
use App\Models\QuickBooksSyncLog;
use App\Services\QuickBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPaymentToQuickBooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly FeePayment $payment) {}

    public function handle(): void
    {
        $school = $this->payment->school;

        $conn = $school->quickBooksConnection;
        if (! $conn) {
            return;
        }

        // Don't duplicate
        if ($this->payment->qb_sales_receipt_id) {
            return;
        }

        $service = QuickBooksService::forSchool($school);
        $student = $this->payment->student;

        // Ensure the student has a QB Customer ID — sync inline if missing
        if (! $student->qb_customer_id) {
            $payload = $service->buildCustomerPayload($student);
            $qbCustomerId = $service->upsertCustomer($payload);
            if ($qbCustomerId) {
                $student->updateQuietly(['qb_customer_id' => $qbCustomerId]);
            }
        }

        $qbCustomerId = $student->fresh()->qb_customer_id;

        if (! $qbCustomerId) {
            $this->logFailure('Could not resolve QB Customer ID for student '.$student->id);

            return;
        }

        // Resolve or create the QB Item for this fee type
        $assignment = $this->payment->assignment;
        $feeName = $assignment?->feeStructure?->name ?? 'School Fee';
        $feeAmount = (int) ($this->payment->amount_paid);

        $qbItemId = $service->getOrCreateItem($feeName, $feeAmount);

        if (! $qbItemId) {
            $this->logFailure("Could not resolve QB Item for fee '{$feeName}'");

            return;
        }

        $receiptPayload = $service->buildSalesReceiptPayload($this->payment, $qbCustomerId, $qbItemId);

        try {
            $result = $service->createSalesReceipt($receiptPayload);

            if (! $result) {
                throw new \RuntimeException('QB returned no SalesReceipt data');
            }

            $this->payment->updateQuietly([
                'qb_sales_receipt_id' => $result['id'],
                'qb_doc_number' => $result['doc_number'],
            ]);

            QuickBooksSyncLog::create([
                'school_id' => $school->id,
                'entity_type' => 'fee_payment',
                'entity_id' => $this->payment->id,
                'qb_entity_type' => 'SalesReceipt',
                'qb_id' => $result['id'],
                'qb_doc_number' => $result['doc_number'],
                'action' => 'create',
                'status' => 'success',
                'request_payload' => $receiptPayload,
                'synced_at' => now(),
            ]);

            Log::info("QB: Payment {$this->payment->id} synced as SalesReceipt {$result['id']}");
        } catch (Throwable $e) {
            $this->logFailure($e->getMessage(), $receiptPayload);
            throw $e;
        }
    }

    private function logFailure(string $message, array $payload = []): void
    {
        QuickBooksSyncLog::create([
            'school_id' => $this->payment->school_id,
            'entity_type' => 'fee_payment',
            'entity_id' => $this->payment->id,
            'qb_entity_type' => 'SalesReceipt',
            'action' => 'create',
            'status' => 'failed',
            'error_message' => $message,
            'request_payload' => $payload,
            'synced_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error("QB sync failed for payment {$this->payment->id}", ['error' => $e->getMessage()]);
    }
}
