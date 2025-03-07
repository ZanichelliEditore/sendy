<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobsCommandTest extends TestCase
{
    /**
     * @test
     * @dataProvider tooManyFailedJobsProvider
     */
    public function tooManyFailedJobsTest($failedJobsCount, $message)
    {
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

    static function tooManyFailedJobsProvider()
    {
        return [
            [0, "It'a all right"],
            [1, "Notification on slack sent"],
        ];
    }
}
