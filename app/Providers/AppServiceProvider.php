<?php

namespace App\Providers;

use App\Models\FeePayment;
use App\Models\Student;
use App\Observers\FeePaymentObserver;
use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Student::observe(StudentObserver::class);
        FeePayment::observe(FeePaymentObserver::class);
    }
}
