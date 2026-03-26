<?php

namespace App\Http\Controllers;

use App\Jobs\QuickBooksBulkSync;
use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use App\Services\QuickBooksService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class QuickBooksController extends Controller
{
    // ─── Settings page ────────────────────────────────────────────────────────

    public function settings(): View
    {
        $school = app('current_school');
        $connection = QuickBooksConnection::where('school_id', $school->id)
            ->whereNull('disconnected_at')
            ->first();

        $recentLogs = QuickBooksSyncLog::where('school_id', $school->id)
            ->latest('synced_at')
            ->limit(50)
            ->get();

        $syncStats = [
            'total' => QuickBooksSyncLog::where('school_id', $school->id)->count(),
            'success' => QuickBooksSyncLog::where('school_id', $school->id)->where('status', 'success')->count(),
            'failed' => QuickBooksSyncLog::where('school_id', $school->id)->where('status', 'failed')->count(),
        ];

        return view('quickbooks.settings', compact('school', 'connection', 'recentLogs', 'syncStats'));
    }

    // ─── OAuth Connect ────────────────────────────────────────────────────────

    public function connect(Request $request): RedirectResponse
    {
        $school = app('current_school');

        // Store school slug in state so we can retrieve it in callback
        $state = base64_encode(json_encode([
            'school_slug' => $school->slug,
            'csrf' => csrf_token(),
        ]));

        Session::put('qb_oauth_state', $state);

        return redirect(QuickBooksService::authorizationUrl($state));
    }

    // ─── OAuth Callback ───────────────────────────────────────────────────────

    public function callback(Request $request): RedirectResponse
    {
        // Validate state to prevent CSRF
        $state = $request->query('state');
        $storedState = Session::pull('qb_oauth_state');

        if (! $state || $state !== $storedState) {
            return redirect()->route('tenant.quickbooks.settings', app('current_school')->slug)
                ->with('error', 'Invalid OAuth state. Please try connecting again.');
        }

        $stateData = json_decode(base64_decode($state), true);
        $schoolSlug = $stateData['school_slug'] ?? null;

        if ($request->has('error')) {
            return redirect()->route('tenant.quickbooks.settings', $schoolSlug)
                ->with('error', 'QuickBooks authorization was denied: '.$request->query('error_description', 'Unknown error'));
        }

        $code = $request->query('code');
        $realmId = $request->query('realmId');

        if (! $code || ! $realmId) {
            return redirect()->route('tenant.quickbooks.settings', $schoolSlug)
                ->with('error', 'Missing authorization code or company ID from QuickBooks.');
        }

        $tokens = QuickBooksService::exchangeCode($code, $realmId);

        if (empty($tokens['access_token'])) {
            return redirect()->route('tenant.quickbooks.settings', $schoolSlug)
                ->with('error', 'Failed to exchange authorization code for tokens.');
        }

        $school = app('current_school');

        // Deactivate any existing connection for this school
        QuickBooksConnection::where('school_id', $school->id)
            ->whereNull('disconnected_at')
            ->update(['disconnected_at' => now()]);

        $environment = config('services.quickbooks.environment', 'production');

        QuickBooksConnection::create([
            'school_id' => $school->id,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'realm_id' => $realmId,
            'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            'environment' => $environment,
            'connected_at' => now(),
        ]);

        return redirect()->route('tenant.quickbooks.settings', $school->slug)
            ->with('success', 'QuickBooks connected successfully! You can now sync your data.');
    }

    // ─── Disconnect ───────────────────────────────────────────────────────────

    public function disconnect(Request $request): RedirectResponse
    {
        $school = app('current_school');

        QuickBooksConnection::where('school_id', $school->id)
            ->whereNull('disconnected_at')
            ->update(['disconnected_at' => now()]);

        return redirect()->route('tenant.quickbooks.settings', $school->slug)
            ->with('success', 'QuickBooks disconnected.');
    }

    // ─── Manual sync ─────────────────────────────────────────────────────────

    public function syncAll(Request $request): RedirectResponse
    {
        $school = app('current_school');

        if (! $school->quickBooksConnection) {
            return back()->with('error', 'No active QuickBooks connection.');
        }

        $mode = $request->input('mode', 'all');

        QuickBooksBulkSync::dispatch($school, $mode)->onQueue('quickbooks');

        return back()->with('success', "QuickBooks bulk sync ({$mode}) queued. This runs in the background.");
    }
}
