<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Http\Repositories\FailedJobRepository;

class TooManyFailedJobsCommandTest extends TestCase
{
    /**
     * @test
     * @dataProvider tooManyFailedJobsProvider
     */
    public function tooManyFailedJobsTest($failedJobsCount, $times)
    {
        $this->app->instance(
            Client::class,
            Mockery::mock(Client::class)
                ->shouldReceive('post')
                ->times($times)
                ->getMock()
        );

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
    }

    static function tooManyFailedJobsProvider()
    {
        return [
            [0, 0],
            [1, 1],
        ];
    }
}
