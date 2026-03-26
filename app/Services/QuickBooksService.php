<?php

namespace App\Services;

use App\Models\QuickBooksConnection;
use App\Models\School;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuickBooksService
{
    private QuickBooksConnection $connection;

    public function __construct(QuickBooksConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Resolve the service for a given school — throws if no active connection.
     */
    public static function forSchool(School $school): self
    {
        $conn = QuickBooksConnection::where('school_id', $school->id)
            ->whereNull('disconnected_at')
            ->firstOrFail();

        $service = new self($conn);
        $service->refreshTokenIfExpired();

        return $service;
    }

    // ─── OAuth helpers ────────────────────────────────────────────────────────

    /**
     * Build the Intuit OAuth authorization URL for the consent screen.
     */
    public static function authorizationUrl(string $state): string
    {
        return 'https://appcenter.intuit.com/connect/oauth2?'.http_build_query([
            'client_id' => config('services.quickbooks.client_id'),
            'redirect_uri' => config('services.quickbooks.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'com.intuit.quickbooks.accounting',
            'state' => $state,
        ]);
    }

    /**
     * Exchange an authorization code for access + refresh tokens.
     */
    public static function exchangeCode(string $code, string $realmId): array
    {
        $response = Http::withBasicAuth(
            config('services.quickbooks.client_id'),
            config('services.quickbooks.client_secret')
        )->asForm()->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.quickbooks.redirect_uri'),
        ]);

        return $response->json() ?? [];
    }

    /**
     * Refresh the access token using the stored refresh token.
     */
    public function refreshTokenIfExpired(): void
    {
        if (! $this->connection->isTokenExpired()) {
            return;
        }

        $response = Http::withBasicAuth(
            config('services.quickbooks.client_id'),
            config('services.quickbooks.client_secret')
        )->asForm()->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->connection->refresh_token,
        ]);

        $data = $response->json();

        if (empty($data['access_token'])) {
            Log::error('QuickBooks token refresh failed', [
                'school_id' => $this->connection->school_id,
                'response' => $data,
            ]);
            throw new \RuntimeException('QuickBooks token refresh failed: '.($data['error'] ?? 'unknown'));
        }

        $this->connection->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $this->connection->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'last_refreshed_at' => now(),
        ]);
    }

    // ─── Customer (maps to Student) ───────────────────────────────────────────

    /**
     * Create or update a QuickBooks Customer for a student.
     *
     * Returns the QB Customer ID on success.
     */
    public function upsertCustomer(array $customerData): ?string
    {
        // Try to find existing by DisplayName
        if (! empty($customerData['Id'])) {
            $body = $this->post('/customer', $customerData);
        } else {
            $body = $this->post('/customer', $customerData);
        }

        return $body['Customer']['Id'] ?? null;
    }

    public function findCustomerByName(string $displayName): ?array
    {
        $query = "SELECT * FROM Customer WHERE DisplayName = '".addslashes($displayName)."' MAXRESULTS 1";
        $result = $this->query($query);

        return $result['QueryResponse']['Customer'][0] ?? null;
    }

    /**
     * Build a QB Customer payload from a Student model.
     */
    public function buildCustomerPayload(\App\Models\Student $student): array
    {
        $payload = [
            'DisplayName' => trim("{$student->first_name} {$student->last_name} [{$student->admission_number}]"),
            'GivenName' => $student->first_name,
            'FamilyName' => $student->last_name,
            'CompanyName' => $student->school->name,
            'Notes' => "Admission No: {$student->admission_number}",
        ];

        if ($student->email) {
            $payload['PrimaryEmailAddr'] = ['Address' => $student->email];
        }

        if ($student->phone) {
            $payload['PrimaryPhone'] = ['FreeFormNumber' => $student->phone];
        }

        // If updating, include the QB ID and SyncToken
        if ($student->qb_customer_id) {
            $existing = $this->get("/customer/{$student->qb_customer_id}");
            $payload['Id'] = $student->qb_customer_id;
            $payload['SyncToken'] = $existing['Customer']['SyncToken'] ?? '0';
            $payload['sparse'] = true;
        }

        return $payload;
    }

    // ─── Items (maps to Fee Structure / Fee Type) ─────────────────────────────

    /**
     * Get or create a QB Service Item for a fee structure.
     * Items are required as line-item products on SalesReceipts.
     */
    public function getOrCreateItem(string $feeName, int $amountKes): ?string
    {
        // Check if item exists by name
        $query = "SELECT * FROM Item WHERE Name = '".addslashes($feeName)."' MAXRESULTS 1";
        $existing = $this->query($query);

        if (! empty($existing['QueryResponse']['Item'][0])) {
            return $existing['QueryResponse']['Item'][0]['Id'];
        }

        // Create it — requires an IncomeAccountRef; use the first income account found
        $accounts = $this->query("SELECT * FROM Account WHERE AccountType = 'Income' MAXRESULTS 1");
        $incomeAccountId = $accounts['QueryResponse']['Account'][0]['Id'] ?? null;

        if (! $incomeAccountId) {
            Log::warning('QuickBooks: no Income account found, cannot create Item', [
                'fee_name' => $feeName,
                'school_id' => $this->connection->school_id,
            ]);

            return null;
        }

        $body = $this->post('/item', [
            'Name' => $feeName,
            'Type' => 'Service',
            'IncomeAccountRef' => ['value' => $incomeAccountId],
            'UnitPrice' => $amountKes,
            'Active' => true,
        ]);

        return $body['Item']['Id'] ?? null;
    }

    // ─── Sales Receipt (maps to FeePayment) ──────────────────────────────────

    /**
     * Create a QB SalesReceipt for a recorded fee payment.
     *
     * Returns ['id' => string, 'doc_number' => string] on success.
     */
    public function createSalesReceipt(array $payload): ?array
    {
        $body = $this->post('/salesreceipt', $payload);
        $receipt = $body['SalesReceipt'] ?? null;

        if (! $receipt) {
            return null;
        }

        return [
            'id' => $receipt['Id'],
            'doc_number' => $receipt['DocNumber'] ?? null,
        ];
    }

    /**
     * Build a QB SalesReceipt payload from a FeePayment model.
     */
    public function buildSalesReceiptPayload(
        \App\Models\FeePayment $payment,
        string $qbCustomerId,
        string $qbItemId
    ): array {
        $depositAccountRef = $this->resolveDepositAccount($payment->payment_method);

        $payload = [
            'CustomerRef' => ['value' => $qbCustomerId],
            'TxnDate' => $payment->payment_date->toDateString(),
            'DocNumber' => $payment->receipt_number,
            'PrivateNote' => "Receipt: {$payment->receipt_number} | Method: {$payment->payment_method}",
            'Line' => [
                [
                    'DetailType' => 'SalesItemLineDetail',
                    'Amount' => (float) $payment->amount_paid,
                    'SalesItemLineDetail' => [
                        'ItemRef' => ['value' => $qbItemId],
                        'UnitPrice' => (float) $payment->amount_paid,
                        'Qty' => 1,
                    ],
                ],
            ],
        ];

        if ($depositAccountRef) {
            $payload['DepositToAccountRef'] = ['value' => $depositAccountRef];
        }

        if ($payment->transaction_reference) {
            $payload['PaymentRefNum'] = $payment->transaction_reference
                ?? $payment->mpesa_receipt_no
                ?? $payment->bank_transfer_ref;
        }

        return $payload;
    }

    /**
     * Void a SalesReceipt in QuickBooks.
     */
    public function voidSalesReceipt(string $qbId): bool
    {
        $existing = $this->get("/salesreceipt/{$qbId}");
        $syncToken = $existing['SalesReceipt']['SyncToken'] ?? '0';

        $result = $this->post('/salesreceipt?operation=void', [
            'Id' => $qbId,
            'SyncToken' => $syncToken,
        ]);

        return isset($result['SalesReceipt']);
    }

    // ─── Private HTTP helpers ─────────────────────────────────────────────────

    private function get(string $endpoint): array
    {
        $url = $this->connection->baseUrl().$this->connection->realm_id.$endpoint;

        try {
            $response = Http::withToken($this->connection->access_token)
                ->acceptJson()
                ->get($url);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('QB GET error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return [];
        }
    }

    private function post(string $endpoint, array $data): array
    {
        $url = $this->connection->baseUrl().$this->connection->realm_id.$endpoint;

        try {
            $response = Http::withToken($this->connection->access_token)
                ->acceptJson()
                ->post($url, $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('QB POST error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return [];
        }
    }

    private function query(string $qbql): array
    {
        $url = $this->connection->baseUrl().$this->connection->realm_id.'/query';

        try {
            $response = Http::withToken($this->connection->access_token)
                ->acceptJson()
                ->get($url, ['query' => $qbql]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('QB query error', ['query' => $qbql, 'error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Resolve a QB deposit account ID by payment method.
     * Falls back to null (QB will use its default undeposited funds account).
     */
    private function resolveDepositAccount(string $paymentMethod): ?string
    {
        $accountName = match ($paymentMethod) {
            'mpesa' => 'Mpesa',
            'bank_transfer' => 'Bank',
            'cash' => 'Petty Cash',
            'cheque' => 'Cheque Receipts',
            default => null,
        };

        if (! $accountName) {
            return null;
        }

        $result = $this->query(
            "SELECT * FROM Account WHERE Name = '".addslashes($accountName)."' MAXRESULTS 1"
        );

        return $result['QueryResponse']['Account'][0]['Id'] ?? null;
    }
}
