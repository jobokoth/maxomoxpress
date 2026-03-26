<?php

use App\Http\Controllers\CommunicationController;
use App\Models\School;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('communication:dispatch-reminders', function () {
    $controller = app(CommunicationController::class);
    $totalSent = 0;

    foreach (School::query()->where('is_active', true)->cursor() as $school) {
        app()->instance('current_school', $school);
        $totalSent += $controller->dispatchDueReminders($school->id);
    }

    $this->info('Event reminders dispatched: ' . $totalSent);
})->purpose('Dispatch due scheduled communication reminders');

Schedule::command('communication:dispatch-reminders')->everyMinute();
