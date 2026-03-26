<?php

namespace App\Observers;

use App\Jobs\SyncStudentToQuickBooks;
use App\Models\QuickBooksConnection;
use App\Models\Student;

class StudentObserver
{
    public function created(Student $student): void
    {
        $this->dispatchIfConnected($student);
    }

    public function updated(Student $student): void
    {
        // Only sync if QB-relevant fields changed
        $watchedFields = ['first_name', 'last_name', 'email', 'phone', 'admission_number'];

        if ($student->wasChanged($watchedFields)) {
            $this->dispatchIfConnected($student);
        }
    }

    private function dispatchIfConnected(Student $student): void
    {
        $connected = QuickBooksConnection::where('school_id', $student->school_id)
            ->whereNull('disconnected_at')
            ->exists();

        if ($connected) {
            SyncStudentToQuickBooks::dispatch($student)->onQueue('quickbooks');
        }
    }
}
