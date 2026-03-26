<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private string $secretKey;

    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key', '');
    }

    // ─── Customer ─────────────────────────────────────────────────────────────

    public function createCustomer(string $email, string $name, string $phone = ''): array
    {
        $response = $this->post('/customer', [
            'email' => $email,
            'first_name' => explode(' ', $name)[0],
            'last_name' => implode(' ', array_slice(explode(' ', $name), 1)) ?: $name,
            'phone' => $phone,
        ]);

        return $response['data'] ?? [];
    }

    public function fetchCustomer(string $emailOrCode): array
    {
        $response = $this->get("/customer/{$emailOrCode}");

        return $response['data'] ?? [];
    }

    // ─── Transactions ─────────────────────────────────────────────────────────

    /**
     * Initialize a one-time payment.
     *
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initializeTransaction(
        string $email,
        int $amountKes,
        string $reference,
        string $callbackUrl,
        array $metadata = []
    ): array {
        $response = $this->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $amountKes * 100, // PayStack uses kobo/cents
            'currency' => 'KES',
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);

        return $response['data'] ?? [];
    }

    public function verifyTransaction(string $reference): array
    {
        $response = $this->get("/transaction/verify/{$reference}");

        return $response['data'] ?? [];
    }

    // ─── Subscriptions ────────────────────────────────────────────────────────

    /**
     * Create a PayStack Plan (monthly recurring billing at a fixed amount).
     */
    public function createPlan(string $name, int $amountKes, string $interval = 'monthly'): array
    {
        $response = $this->post('/plan', [
            'name' => $name,
            'amount' => $amountKes * 100,
            'currency' => 'KES',
            'interval' => $interval,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * Subscribe a customer to a plan using a previously authorized card.
     */
    public function createSubscription(
        string $customerCode,
        string $planCode,
        string $authorizationCode
    ): array {
        $response = $this->post('/subscription', [
            'customer' => $customerCode,
            'plan' => $planCode,
            'authorization' => $authorizationCode,
        ]);

        return $response['data'] ?? [];
    }

    public function fetchSubscription(string $subscriptionCode): array
    {
        $response = $this->get("/subscription/{$subscriptionCode}");

        return $response['data'] ?? [];
    }

    public function cancelSubscription(string $subscriptionCode, string $emailToken): array
    {
        return $this->post('/subscription/disable', [
            'code' => $subscriptionCode,
            'token' => $emailToken,
        ]);
    }

    // ─── Webhook Verification ─────────────────────────────────────────────────

    /**
     * Verify that a webhook request came from PayStack.
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        $expected = hash_hmac('sha512', $body, $this->secretKey);

        return hash_equals($expected, $signature);
    }

    // ─── HTTP helpers ─────────────────────────────────────────────────────────

    private function post(string $endpoint, array $data): array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->post($this->baseUrl.$endpoint, $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('PayStack API error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function get(string $endpoint): array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->get($this->baseUrl.$endpoint);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('PayStack API error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
