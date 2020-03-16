<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\FailedJob;
use Illuminate\Pagination\Paginator;
use App\Http\Repositories\FailedJobRepository;

class FailedJobTest extends TestCase
{
    private function getJsonFragment(FailedJob $failedJob = null):array {
        if(is_null($failedJob)) {
            return [
                "data" => []
            ];
        }

        return [
            "data" => [
                [
                    "exception" => $failedJob->exception,
                    "failed_at" => $failedJob->failed_at,
                    "id" => $failedJob->id,
                    "payload" => $failedJob->payload
                ]
            ]
        ];
    }

    /**
    * @test
    * @return void
    */
    public function testSuccesfullyListFailedJob()
    {
        $failedJob = factory(FailedJob::class)->make();

        $paginator = new Paginator([$failedJob], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
                        ->shouldReceive([
                            'all' => $paginator
                        ])
                        ->withAnyArgs()
                        ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->json('GET', '/api/failedJobs');
        $response->assertStatus(200);
    }

    /**
    * @test
    * @return void
    */
    public function testListFailedJobInvalidData()
    {
        $paginator = new Paginator([], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
                        ->shouldReceive([
                            'all' => $paginator
                        ])
                        ->withAnyArgs()
                        ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);

        $response = $this->json('GET', '/api/failedJobs?limit=a');
        $response->assertStatus(422)->assertJsonFragment(["errors" => ["limit" => ["The limit must be an integer."]],"message" => "Data is invalid"]);

        $response = $this->json('GET', '/api/failedJobs?limit=1&order=sacc');
        $response->assertStatus(422)->assertJsonFragment(["errors" => ["order" => ["The selected order is invalid."]],"message" => "Data is invalid"]);
    }


    /**
     * @test
     * @return void
     */
    public function testListNoFailedJob()
    {
        $paginator = new Paginator([], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
                ->shouldReceive([
                    'all' => $paginator
                ])
                ->withAnyArgs()
                ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->get('/api/failedJobs');
        $response->assertStatus(200)->assertJsonFragment($this->getJsonFragment());
    }


    /**
     * @test
     * @return void
     */
    public function testDestroyUnrealFailedJob()
    {
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
                ->shouldReceive([
                    'find' => null
                ])
                ->withAnyArgs()
                ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->delete('/api/failedJobs/' . 1);
        $response->assertStatus(404);
    }

    /**
     * @test
     * @return void
     */
    public function testRetryUnrealFailedJob()
    {
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
                ->shouldReceive([
                    'find' => null
                ])
                ->withAnyArgs()
                ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->get('/api/failedJobs/retry/' . 1);
        $response->assertStatus(404);
    }

}
