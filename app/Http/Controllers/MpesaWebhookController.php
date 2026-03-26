<?php

namespace App\Http\Controllers;

use App\Models\FeeAssignment;
use App\Models\FeePayment;
use App\Models\MpesaTransaction;
use App\Models\School;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    /**
     * C2B Validation URL.
     *
     * Safaricom sends this BEFORE accepting the payment. We can reject here.
     * Respond with ResultCode 0 to accept, non-zero to reject.
     */
    public function validation(Request $request, string $schoolSlug): JsonResponse
    {
        $school = School::where('slug', $schoolSlug)->where('is_active', true)->first();

        if (! $school) {
            return response()->json(['ResultCode' => 'C2B00011', 'ResultDesc' => 'School not found.']);
        }

        Log::info("Mpesa C2B validation hit for school: {$school->slug}", $request->all());

        // Accept all — you can add custom validation logic here
        // e.g. check BillRefNumber matches a student admission number
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * C2B Confirmation URL.
     *
     * Safaricom sends this AFTER the payment is processed and irrevocable.
     * We MUST persist and respond quickly (< 5s) or Safaricom will retry.
     */
    public function confirmation(Request $request, string $schoolSlug): JsonResponse
    {
        $payload = $request->all();
        Log::info("Mpesa C2B confirmation for school: {$schoolSlug}", $payload);

        $school = School::where('slug', $schoolSlug)->where('is_active', true)->first();

        if (! $school) {
            Log::warning("C2B confirmation for unknown school slug: {$schoolSlug}");

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        try {
            $data = (new MpesaService(
                \App\Models\MpesaCredential::where('school_id', $school->id)
                    ->orWhereNull('school_id')
                    ->first()
            ))->parseC2bConfirmation($payload);

            DB::transaction(function () use ($data, $school, $payload): void {
                // Avoid duplicate processing
                if (MpesaTransaction::where('mpesa_receipt_number', $data['mpesa_receipt_number'])->exists()) {
                    return;
                }

                $txn = MpesaTransaction::create([
                    'school_id' => $school->id,
                    'type' => 'c2b',
                    'status' => 'completed',
                    'mpesa_receipt_number' => $data['mpesa_receipt_number'],
                    'transaction_id' => $data['transaction_id'],
                    'amount' => $data['amount'],
                    'phone_number' => $data['phone_number'],
                    'bill_ref_number' => $data['bill_ref_number'],
                    'paybill_shortcode' => $data['paybill_shortcode'],
                    'transaction_time' => $data['transaction_time'],
                    'callback_received_at' => now(),
                    'raw_payload' => $payload,
                    'result_code' => '0',
                    'result_description' => 'Completed',
                ]);

                // Try to auto-match to a student via bill_ref_number (admission number)
                $this->tryAutoMatch($txn, $school);
            });
        } catch (\Throwable $e) {
            Log::error('C2B confirmation processing error', [
                'error' => $e->getMessage(),
                'school' => $schoolSlug,
                'payload' => $payload,
            ]);
        }

        // Always acknowledge to Safaricom — don't let errors cause retries
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * STK Push callback.
     *
     * Safaricom calls this URL with the result of an STK push request.
     */
    public function stkCallback(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Mpesa STK callback received', $payload);

        try {
            $merchantRequestId = $payload['Body']['stkCallback']['MerchantRequestID'] ?? null;
            $checkoutRequestId = $payload['Body']['stkCallback']['CheckoutRequestID'] ?? null;

            $txn = MpesaTransaction::where('merchant_request_id', $merchantRequestId)
                ->orWhere('checkout_request_id', $checkoutRequestId)
                ->first();

            if (! $txn) {
                Log::warning('STK callback for unknown transaction', compact('merchantRequestId', 'checkoutRequestId'));

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            // Get the school's credential to parse the callback
            $cred = \App\Models\MpesaCredential::where('school_id', $txn->school_id)
                ->orWhereNull('school_id')
                ->first();

            $parsed = (new MpesaService($cred))->parseStkCallback($payload);

            DB::transaction(function () use ($txn, $parsed, $payload): void {
                if (! $parsed['success']) {
                    $txn->update([
                        'status' => 'failed',
                        'result_code' => $parsed['result_code'],
                        'result_description' => $parsed['result_description'],
                        'callback_received_at' => now(),
                        'raw_payload' => $payload,
                    ]);

                    return;
                }

                $txn->update([
                    'status' => 'completed',
                    'mpesa_receipt_number' => $parsed['mpesa_receipt_number'],
                    'amount' => $parsed['amount'],
                    'phone_number' => $parsed['phone_number'],
                    'transaction_time' => $parsed['transaction_time'],
                    'callback_received_at' => now(),
                    'raw_payload' => $payload,
                    'result_code' => '0',
                    'result_description' => $parsed['result_description'],
                ]);

                // If this STK push was tied to a fee assignment, auto-record the payment
                if ($txn->fee_assignment_id) {
                    $assignment = FeeAssignment::find($txn->fee_assignment_id);
                    if ($assignment && $txn->amount > 0) {
                        $payment = FeePayment::create([
                            'school_id' => $txn->school_id,
                            'student_id' => $txn->student_id ?? $assignment->student_id,
                            'fee_assignment_id' => $assignment->id,
                            'amount_paid' => min($txn->amount, $assignment->balance_amount),
                            'payment_date' => $txn->transaction_time ?? now(),
                            'payment_method' => 'mpesa',
                            'transaction_reference' => $parsed['mpesa_receipt_number'],
                            'mpesa_receipt_no' => $parsed['mpesa_receipt_number'],
                            'mpesa_phone' => $parsed['phone_number'],
                            'mpesa_transaction_id' => $txn->id,
                            'receipt_number' => $this->generateReceiptNumber(),
                        ]);

                        $txn->update(['fee_payment_id' => $payment->id]);
                        app(FinanceController::class)->recalculateAssignmentPublic($assignment->fresh());
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('STK callback processing error', ['error' => $e->getMessage(), 'payload' => $payload]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function tryAutoMatch(MpesaTransaction $txn, School $school): void
    {
        if (! $txn->bill_ref_number) {
            return;
        }

        // Look up student by admission number in the bill reference
        $student = \App\Models\Student::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('admission_number', trim($txn->bill_ref_number))
            ->first();

        if (! $student) {
            return;
        }

        // Find the most recent outstanding fee assignment for this student
        $assignment = FeeAssignment::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('balance_amount', '>', 0)
            ->orderByDesc('due_date')
            ->first();

        if (! $assignment) {
            $txn->update(['student_id' => $student->id]);

            return;
        }

        $payment = FeePayment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_assignment_id' => $assignment->id,
            'amount_paid' => min($txn->amount, $assignment->balance_amount),
            'payment_date' => $txn->transaction_time ?? now(),
            'payment_method' => 'mpesa',
            'transaction_reference' => $txn->mpesa_receipt_number,
            'mpesa_receipt_no' => $txn->mpesa_receipt_number,
            'mpesa_phone' => $txn->phone_number,
            'mpesa_transaction_id' => $txn->id,
            'receipt_number' => $this->generateReceiptNumber(),
        ]);

        $txn->update([
            'student_id' => $student->id,
            'fee_assignment_id' => $assignment->id,
            'fee_payment_id' => $payment->id,
        ]);
    }

    private function generateReceiptNumber(): string
    {
        $prefix = 'RCP-'.now()->format('Ymd');
        $last = FeePayment::withoutGlobalScopes()
            ->where('receipt_number', 'like', $prefix.'-%')
            ->latest('id')
            ->value('receipt_number');

        $next = $last ? ((int) last(explode('-', $last))) + 1 : 1;

        return sprintf('%s-%04d', $prefix, $next);
    }
}
