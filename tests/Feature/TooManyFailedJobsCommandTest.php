<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Http\Repositories\FailedJobRepository;
use App\Utils\FailedJobsUtils;
use Illuminate\Support\Facades\Cache;

class TooManyFailedJobsCommandTest extends TestCase
{
    #[DataProvider('tooManyFailedJobsProvider')]
    public function testTooManyFailedJobsTest($failedJobsCount, $message)
    {
        Log::shouldReceive('channel->error')->times($failedJobsCount);

        if ($failedJobsCount) {
            Cache::shouldReceive("get")->once()->with(FailedJobsUtils::$cacheKey)->andReturn(null);
            Cache::shouldReceive("set")->once();
        } else {
            Cache::shouldNotReceive("get");
            Cache::shouldNotReceive("set");
        }

        $this->mock(FailedJobRepository::class, function ($mock) use ($failedJobsCount) {
            $mock->shouldReceive("count")->once()->andReturn($failedJobsCount);
        });

        $this->artisan('check:failed-jobs')->expectsOutput($message)->assertOk();
    }

    public function testCommandShouldNotSendNotificationWhenJobsCountDoesntChange()
    {
        $count = 5;

        Cache::shouldReceive("get")->once()->with(FailedJobsUtils::$cacheKey)->andReturn($count);
        Cache::shouldNotReceive("set");

        $this->mock(FailedJobRepository::class, function ($mock) use ($count) {
            $mock->shouldReceive("count")->once()->andReturn($count);
        });

        $this->artisan('check:failed-jobs')->expectsOutput("$count failed jobs - skip notification")->assertOk();
    }

    static public function tooManyFailedJobsProvider()
    {
        return [
            [0, "It's all right"],
            [1, "Notification on slack sent"],
        ];
    }
}
