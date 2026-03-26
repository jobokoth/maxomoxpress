<?php

namespace App\Observers;

use App\Jobs\SyncPaymentToQuickBooks;
use App\Models\FeePayment;
use App\Models\QuickBooksConnection;

class FeePaymentObserver
{
    public function created(FeePayment $payment): void
    {
        $connected = QuickBooksConnection::where('school_id', $payment->school_id)
            ->whereNull('disconnected_at')
            ->exists();

        if ($connected) {
            SyncPaymentToQuickBooks::dispatch($payment)->onQueue('quickbooks');
        }
    }
}
