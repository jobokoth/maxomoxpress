<?php

namespace App\Http\Controllers;

use App\Models\MpesaCredential;
use App\Models\SchoolPaymentConfig;
use App\Services\MpesaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function show(): View
    {
        $school = app('current_school');
        $config = SchoolPaymentConfig::firstOrNew(['school_id' => $school->id]);
        $cred = MpesaCredential::where('school_id', $school->id)->first();

        return view('payments.settings', compact('school', 'config', 'cred'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = app('current_school');

        $validated = $request->validate([
            // Mpesa
            'mpesa_mode' => ['required', 'in:disabled,own_daraja,bank_paybill,platform'],
            'mpesa_shortcode' => ['nullable', 'string', 'max:20'],
            'mpesa_account_reference' => ['nullable', 'string', 'max:100'],
            // Mpesa credentials (only for own_daraja / bank_paybill)
            'consumer_key' => ['nullable', 'string'],
            'consumer_secret' => ['nullable', 'string'],
            'passkey' => ['nullable', 'string'],
            'environment' => ['nullable', 'in:sandbox,production'],
            // Bank transfer
            'bank_transfer_enabled' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_branch' => ['nullable', 'string', 'max:100'],
            'bank_swift_code' => ['nullable', 'string', 'max:20'],
            'accepts_rtgs' => ['nullable', 'boolean'],
            'accepts_swift' => ['nullable', 'boolean'],
            'accepts_pesalink' => ['nullable', 'boolean'],
            // Cheques
            'cheques_enabled' => ['nullable', 'boolean'],
            'cheques_payable_to' => ['nullable', 'string', 'max:150'],
            // Cash
            'cash_enabled' => ['nullable', 'boolean'],
        ]);

        // Save payment config
        $config = SchoolPaymentConfig::updateOrCreate(
            ['school_id' => $school->id],
            [
                'mpesa_mode' => $validated['mpesa_mode'],
                'mpesa_shortcode' => $validated['mpesa_shortcode'] ?? null,
                'mpesa_account_reference' => $validated['mpesa_account_reference'] ?? null,
                'bank_transfer_enabled' => $request->boolean('bank_transfer_enabled'),
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'bank_branch' => $validated['bank_branch'] ?? null,
                'bank_swift_code' => $validated['bank_swift_code'] ?? null,
                'accepts_rtgs' => $request->boolean('accepts_rtgs'),
                'accepts_swift' => $request->boolean('accepts_swift'),
                'accepts_pesalink' => $request->boolean('accepts_pesalink'),
                'cheques_enabled' => $request->boolean('cheques_enabled'),
                'cheques_payable_to' => $validated['cheques_payable_to'] ?? null,
                'cash_enabled' => $request->boolean('cash_enabled', true),
            ]
        );

        // Save Daraja credentials if mode requires them
        if (in_array($validated['mpesa_mode'], ['own_daraja', 'bank_paybill'])) {
            $credData = array_filter([
                'shortcode' => $validated['mpesa_shortcode'],
                'shortcode_type' => 'paybill',
                'environment' => $validated['environment'] ?? 'production',
            ]);

            // Only update secrets if provided (don't overwrite with blanks)
            if (! empty($validated['consumer_key'])) {
                $credData['consumer_key'] = $validated['consumer_key'];
            }
            if (! empty($validated['consumer_secret'])) {
                $credData['consumer_secret'] = $validated['consumer_secret'];
            }
            if (! empty($validated['passkey'])) {
                $credData['passkey'] = $validated['passkey'];
            }

            $cred = MpesaCredential::updateOrCreate(
                ['school_id' => $school->id],
                $credData
            );

            // Auto-register Daraja URLs if we have full credentials
            if (! empty($validated['consumer_key']) && ! empty($validated['consumer_secret'])) {
                $this->registerMpesaUrls($school, $cred, $config);
            }
        }

        return back()->with('success', 'Payment settings saved successfully.');
    }

    /** Trigger Safaricom RegisterURL API and mark the config as registered. */
    private function registerMpesaUrls($school, MpesaCredential $cred, SchoolPaymentConfig $config): void
    {
        try {
            $validationUrl = route('mpesa.validation', $school->slug);
            $confirmationUrl = route('mpesa.confirmation', $school->slug);

            $service = new MpesaService($cred);
            $response = $service->registerUrls($validationUrl, $confirmationUrl);

            $success = isset($response['ResponseDescription']) &&
                str_contains(strtolower($response['ResponseDescription']), 'success');

            $config->update([
                'mpesa_urls_registered' => $success,
                'mpesa_urls_registered_at' => $success ? now() : null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Mpesa URL registration failed', [
                'school' => $school->slug,
                'error' => $e->getMessage(),
            ]);
            // Don't bubble — settings still saved, user can retry
        }
    }

    /** Manually re-trigger URL registration (e.g. if credentials changed). */
    public function registerUrls(): RedirectResponse
    {
        $school = app('current_school');
        $cred = MpesaCredential::where('school_id', $school->id)->first();
        $config = SchoolPaymentConfig::where('school_id', $school->id)->first();

        if (! $cred || ! $config) {
            return back()->with('error', 'Payment configuration not found.');
        }

        $this->registerMpesaUrls($school, $cred, $config);

        return back()->with('success', 'Safaricom URL registration attempted. Check status below.');
    }
}
