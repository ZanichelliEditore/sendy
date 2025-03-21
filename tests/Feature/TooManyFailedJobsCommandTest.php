<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobsCommandTest extends TestCase
{
    #[DataProvider('tooManyFailedJobsProvider')]
    public function testTooManyFailedJobsTest($failedJobsCount, $message)
    {
        Log::shouldReceive('channel->error')->times($failedJobsCount);

        $this->app->instance(
            'App\Http\Repositories\FailedJobRepository',
            Mockery::mock(FailedJobRepository::class)->makePartial()
                ->shouldReceive([
                    'count' => $failedJobsCount
                ])
                ->once()
                ->getMock()
        );

        $this->artisan('check:failed-jobs')->expectsOutput($message)->assertOk();
    }

    static public function tooManyFailedJobsProvider()
    {
        return [
            [0, "It'a all right"],
            [1, "Notification on slack sent"],
        ];
    }
}
