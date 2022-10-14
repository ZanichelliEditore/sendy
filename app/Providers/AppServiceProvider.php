<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::failing(function (JobFailed $failedJob) {
            Log::error("Job " . $failedJob->job->uuid() . " failed", [
                "payload" => $failedJob->job->payload(),
                "exceptionMessage" => optional($failedJob->exception)->getMessage()
            ]);
        });
    }
}
