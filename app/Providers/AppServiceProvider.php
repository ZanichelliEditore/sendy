<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Scramble::ignoreDefaultRoutes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::$validateKeyPermissions = false;

        Queue::failing(function (JobFailed $failedJob) {
            Log::error("Job " . $failedJob->job->uuid() . " failed", [
                "payload" => $failedJob->job->payload(),
                "exceptionMessage" => optional($failedJob->exception)->getMessage()
            ]);
        });
    }
}
