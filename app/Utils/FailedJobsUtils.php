<?php

namespace App\Utils;

use Illuminate\Support\Facades\Cache;

trait FailedJobsUtils
{
    static $cacheKey = "failed_jobs_count";

    protected function getFailedJobsCache(): mixed
    {
        return Cache::get(self::$cacheKey);
    }

    protected function setFailedJobsCache(int $count): void
    {
        Cache::set(self::$cacheKey, $count, 86400);
    }

    protected function cleanFailedJobsCache(): void
    {
        Cache::forget(self::$cacheKey);
    }
}
