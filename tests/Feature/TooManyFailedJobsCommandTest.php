<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Notifications\SlackNotification;
use Illuminate\Support\Facades\Notification;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobsCommandTest extends TestCase
{
    /**
     * @test
     * @dataProvider tooManyFailedJobsProvider
     */
    public function tooManyFailedJobsTest($failedJobsCount, $notificationSended)
    {
        Notification::fake();

        $this->app->instance(
            'App\Http\Repositories\FailedJobRepository',
            Mockery::mock(FailedJobRepository::class)->makePartial()
                ->shouldReceive([
                    'count' => $failedJobsCount
                ])
                ->once()
                ->getMock()
        );

        $this->artisan('check:failed-jobs')->assertExitCode(0);
        Notification::assertCount($notificationSended);
    }

    static function tooManyFailedJobsProvider()
    {
        return [
            [0, 0],
            [1, 1],
        ];
    }
}
