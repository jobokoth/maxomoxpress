<?php

namespace App\Http\Controllers;

use App\Models\PlatformInvoice;
use App\Models\PlatformSubscription;
use App\Models\School;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformBillingController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    // ─── Payment Initialization ───────────────────────────────────────────────

    /**
     * Generate a PayStack payment link for a school's first subscription payment.
     *
     * Called from the billing page; the school admin completes payment,
     * PayStack redirects back, and the webhook activates the subscription.
     */
    public function initiate(Request $request, string $schoolSlug): RedirectResponse
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $pricing = PlatformSubscription::calculatePricing($school->students()->count());

        // Ensure or create PayStack customer
        $sub = PlatformSubscription::firstOrNew(['school_id' => $school->id]);
        $customerCode = $sub->paystack_customer_code;

        if (! $customerCode) {
            $customer = $this->paystack->createCustomer(
                $school->email,
                $school->name,
                $school->phone ?? ''
            );
            $customerCode = $customer['customer_code'] ?? null;
        }

        $reference = 'MASOMO-'.strtoupper($school->slug).'-'.now()->format('YmdHis');

        $tx = $this->paystack->initializeTransaction(
            email: $school->email,
            amountKes: $pricing['amount_kes'],
            reference: $reference,
            callbackUrl: route('platform.billing.callback', $schoolSlug),
            metadata: [
                'school_id' => $school->id,
                'school_slug' => $school->slug,
                'tier' => $pricing['tier'],
            ]
        );

        if (empty($tx['authorization_url'])) {
            return back()->with('error', 'Could not initialize payment. Check PayStack configuration.');
        }

        // Persist / update subscription record while we wait for payment
        $sub->fill([
            'school_id' => $school->id,
            'status' => $sub->status ?? 'trial',
            'tier' => $pricing['tier'],
            'amount_kes' => $pricing['amount_kes'],
            'billing_cycle' => 'monthly',
            'student_count_at_billing' => $school->students()->count(),
            'paystack_customer_code' => $customerCode,
        ])->save();

        // Create a draft invoice
        PlatformInvoice::create([
            'school_id' => $school->id,
            'platform_subscription_id' => $sub->id,
            'invoice_number' => PlatformInvoice::nextInvoiceNumber(),
            'status' => 'draft',
            'amount_kes' => $pricing['amount_kes'],
            'tier' => $pricing['tier'],
            'student_count' => $school->students()->count(),
            'paystack_reference' => $reference,
            'issued_at' => now(),
            'due_at' => now()->addDays(7),
        ]);

        return redirect($tx['authorization_url']);
    }

    /**
     * PayStack redirects here after the customer completes (or abandons) payment.
     */
    public function callback(Request $request, string $schoolSlug): RedirectResponse
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect('/')->with('error', 'Payment reference missing.');
        }

        $data = $this->paystack->verifyTransaction($reference);

        if (($data['status'] ?? '') !== 'success') {
            return back()->with('error', 'Payment was not successful. Please try again.');
        }

        $this->activateFromPaystackData($data);

        return redirect()->route('onboarding')->with('success', 'Subscription activated! Welcome to MasomoPlus.');
    }

    // ─── Manual charge from platform admin ───────────────────────────────────

    /**
     * Triggered from Filament table action — charge a school now using stored authorization.
     */
    public function chargeSchool(PlatformSubscription $subscription): void
    {
        if (! $subscription->paystack_authorization_code) {
            return;
        }

        $reference = 'MASOMO-RECURRING-'.$subscription->id.'-'.now()->format('YmdHis');

        $tx = $this->paystack->initializeTransaction(
            email: $subscription->school->email,
            amountKes: $subscription->amount_kes,
            reference: $reference,
            callbackUrl: url('/platform-admin'),
        );

        // Verification would come via webhook; log for now
        Log::info('Manual charge initiated', ['subscription' => $subscription->id, 'reference' => $reference]);
    }

    // ─── PayStack Webhook ─────────────────────────────────────────────────────

    public function webhook(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature', '');
        $body = $request->getContent();

        if (! $this->paystack->verifyWebhookSignature($body, $signature)) {
            Log::warning('PayStack webhook signature mismatch');

            return response()->json(['status' => 'error'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        Log::info("PayStack webhook: {$event}", ['reference' => $data['reference'] ?? null]);

        match ($event) {
            'charge.success' => $this->activateFromPaystackData($data),
            'subscription.disable' => $this->handleSubscriptionDisabled($data),
            'invoice.payment_failed' => $this->handlePaymentFailed($data),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function activateFromPaystackData(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (! $reference) {
            return;
        }

        DB::transaction(function () use ($data, $reference): void {
            $invoice = PlatformInvoice::where('paystack_reference', $reference)->first();

            if (! $invoice) {
                Log::warning('PayStack payment for unknown invoice', compact('reference'));

                return;
            }

            if ($invoice->status === 'paid') {
                return; // idempotent
            }

            $invoice->update([
                'status' => 'paid',
                'paystack_transaction_id' => $data['id'] ?? null,
                'paystack_payload' => $data,
                'paid_at' => now(),
            ]);

            $sub = $invoice->subscription;
            $sub->update([
                'status' => 'active',
                'paystack_authorization_code' => $data['authorization']['authorization_code'] ?? $sub->paystack_authorization_code,
                'paystack_email_token' => $data['plan_object']['email_token'] ?? $sub->paystack_email_token,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
                'next_billing_date' => now()->addMonth(),
                'last_payment_at' => now(),
                'last_payment_amount_kes' => $invoice->amount_kes,
            ]);

            // Mark school as no longer on trial
            $sub->school->update(['is_trial' => false]);
        });
    }

    private function handleSubscriptionDisabled(array $data): void
    {
        $code = $data['subscription_code'] ?? null;
        if (! $code) {
            return;
        }

        PlatformSubscription::where('paystack_subscription_code', $code)
            ->update(['status' => 'cancelled']);
    }

    private function handlePaymentFailed(array $data): void
    {
        $code = $data['subscription']['subscription_code'] ?? null;
        if (! $code) {
            return;
        }

        PlatformSubscription::where('paystack_subscription_code', $code)
            ->update(['status' => 'past_due']);
    }
}
