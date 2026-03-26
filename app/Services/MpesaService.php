<?php

namespace App\Services;

use App\Models\MpesaCredential;
use App\Models\School;
use App\Models\SchoolPaymentConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaService
{
    private MpesaCredential $cred;

    private string $baseUrl;

    /**
     * Resolve the correct Daraja credentials for a school.
     *
     * Resolution order:
     *  1. School has `own_daraja` mode → use school's own MpesaCredential
     *  2. School has `platform` mode   → use the platform-level credential (school_id IS NULL)
     *  3. School has `bank_paybill`    → same as own_daraja (school owns the paybill)
     */
    public static function forSchool(School $school): self
    {
        $config = SchoolPaymentConfig::where('school_id', $school->id)->first();

        if (! $config || $config->mpesa_mode === 'disabled') {
            throw new \RuntimeException("Mpesa is not enabled for school: {$school->name}");
        }

        if ($config->mpesa_mode === 'platform') {
            $cred = MpesaCredential::whereNull('school_id')->firstOrFail();
        } else {
            // own_daraja or bank_paybill
            $cred = MpesaCredential::where('school_id', $school->id)->firstOrFail();
        }

        return new self($cred);
    }

    public function __construct(MpesaCredential $credential)
    {
        $this->cred = $credential;
        $this->baseUrl = $credential->baseUrl();
    }

    // ─── OAuth ──────────────────────────────────────────────────────────────────

    /**
     * Get a cached OAuth access token. Tokens are valid for 3600s;
     * we cache for 3500s to avoid edge cases.
     */
    public function accessToken(): string
    {
        $cacheKey = "mpesa_token_{$this->cred->id}";

        return Cache::remember($cacheKey, 3500, function (): string {
            $response = Http::withBasicAuth(
                $this->cred->consumer_key,
                $this->cred->consumer_secret
            )->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if (! $response->successful()) {
                Log::error('Mpesa OAuth failed', ['response' => $response->body()]);
                throw new \RuntimeException('Failed to obtain Mpesa access token.');
            }

            return $response->json('access_token');
        });
    }

    // ─── STK Push (Lipa Na Mpesa Online) ────────────────────────────────────────

    /**
     * Initiate an STK push to the customer's phone.
     *
     * @param  string  $phone  Phone in format 2547XXXXXXXX
     * @param  float  $amount  Amount in KES (must be integer value for Mpesa)
     * @param  string  $accountRef  Customer-visible account reference (e.g. student admission number)
     * @param  string  $description  Short description shown on phone
     * @param  string  $callbackUrl  URL Safaricom will call with the result
     */
    public function stkPush(
        string $phone,
        float $amount,
        string $accountRef,
        string $description,
        string $callbackUrl
    ): array {
        $shortcode = $this->cred->shortcode;
        $passkey = $this->cred->passkey;

        if (! $passkey) {
            throw new \RuntimeException('Passkey not configured for STK push.');
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $this->cred->shortcode_type === 'till'
                ? 'CustomerBuyGoodsOnline'
                : 'CustomerPayBillOnline',
            'Amount' => (int) ceil($amount),
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => Str::limit($accountRef, 12),
            'TransactionDesc' => Str::limit($description, 13),
        ];

        $response = $this->post('/mpesa/stkpush/v1/processrequest', $payload);

        Log::info('Mpesa STK push initiated', [
            'shortcode' => $shortcode,
            'phone' => $phone,
            'amount' => $amount,
            'response' => $response,
        ]);

        return $response;
    }

    /**
     * Query the status of an STK push transaction.
     */
    public function stkQuery(string $checkoutRequestId): array
    {
        $shortcode = $this->cred->shortcode;
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$this->cred->passkey.$timestamp);

        return $this->post('/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);
    }

    // ─── C2B (Customer to Business) ─────────────────────────────────────────────

    /**
     * Register validation and confirmation URLs with Safaricom.
     * Call this once when a school adds/updates their paybill.
     *
     * @param  string  $validationUrl  Safaricom hits this before accepting payment
     * @param  string  $confirmationUrl  Safaricom hits this after payment is confirmed
     */
    public function registerUrls(string $validationUrl, string $confirmationUrl): array
    {
        $payload = [
            'ShortCode' => $this->cred->shortcode,
            'ResponseType' => 'Completed', // or 'Cancelled' — Complete means accept all if validation URL is down
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl,
        ];

        $response = $this->post('/mpesa/c2b/v1/registerurl', $payload);

        Log::info('Mpesa C2B URLs registered', [
            'shortcode' => $this->cred->shortcode,
            'response' => $response,
        ]);

        return $response;
    }

    // ─── Callback Parsing ────────────────────────────────────────────────────────

    /**
     * Parse an STK push callback payload into a structured array.
     * Returns null if the callback indicates failure.
     */
    public function parseStkCallback(array $payload): ?array
    {
        $body = $payload['Body']['stkCallback'] ?? null;

        if (! $body) {
            return null;
        }

        $resultCode = $body['ResultCode'] ?? null;
        $resultDesc = $body['ResultDesc'] ?? '';
        $merchantRequestId = $body['MerchantRequestID'] ?? null;
        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;

        if ($resultCode !== 0) {
            return [
                'success' => false,
                'result_code' => (string) $resultCode,
                'result_description' => $resultDesc,
                'merchant_request_id' => $merchantRequestId,
                'checkout_request_id' => $checkoutRequestId,
            ];
        }

        // Extract metadata items
        $items = collect($body['CallbackMetadata']['Item'] ?? [])
            ->keyBy('Name')
            ->map(fn ($item) => $item['Value'] ?? null);

        return [
            'success' => true,
            'result_code' => '0',
            'result_description' => $resultDesc,
            'merchant_request_id' => $merchantRequestId,
            'checkout_request_id' => $checkoutRequestId,
            'mpesa_receipt_number' => $items->get('MpesaReceiptNumber'),
            'amount' => (float) $items->get('Amount', 0),
            'phone_number' => (string) $items->get('PhoneNumber', ''),
            'transaction_time' => $items->get('TransactionDate')
                ? \Carbon\Carbon::createFromFormat('YmdHis', (string) $items->get('TransactionDate'))
                : null,
        ];
    }

    /**
     * Parse a C2B confirmation callback payload.
     */
    public function parseC2bConfirmation(array $payload): array
    {
        return [
            'transaction_id' => $payload['TransID'] ?? null,
            'mpesa_receipt_number' => $payload['TransID'] ?? null,
            'amount' => (float) ($payload['TransAmount'] ?? 0),
            'phone_number' => $payload['MSISDN'] ?? null,
            'bill_ref_number' => $payload['BillRefNumber'] ?? null,
            'paybill_shortcode' => $payload['BusinessShortCode'] ?? null,
            'first_name' => $payload['FirstName'] ?? null,
            'transaction_time' => isset($payload['TransTime'])
                ? \Carbon\Carbon::createFromFormat('YmdHis', (string) $payload['TransTime'])
                : null,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Format a Kenyan phone number to the 2547XXXXXXXX format Daraja expects.
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        }

        return $phone;
    }

    private function post(string $endpoint, array $payload): array
    {
        $response = Http::withToken($this->accessToken())
            ->post($this->baseUrl.$endpoint, $payload);

        if ($response->serverError()) {
            Log::error("Mpesa API error on {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException("Mpesa API request failed: {$response->status()}");
        }

        return $response->json() ?? [];
    }
}
