<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\FailedJob;
use App\Http\Repositories\FailedJobRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class FailedJobTest extends TestCase
{
    private function getJsonFragment(?FailedJob $failedJob = null): array
    {
        if (is_null($failedJob)) {
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

    public function testSuccesfullyListFailedJob()
    {
        $failedJob = FailedJob::factory()->make();

        $paginator = new LengthAwarePaginator([$failedJob], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
            ->shouldReceive([
                'allPaginated' => $paginator
            ])
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->json('GET', '/api/failedJobs');
        $response->assertStatus(200);
    }

    public function testListFailedJobInvalidData()
    {
        $paginator = new LengthAwarePaginator([], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
            ->shouldReceive([
                'allPaginated' => $paginator
            ])
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);

        $response = $this->json('GET', '/api/failedJobs?limit=a');
        $response->assertStatus(422)->assertJsonFragment(["errors" => ["limit" => ["The limit must be an integer."]], "message" => "Data is invalid"]);

        $response = $this->json('GET', '/api/failedJobs?limit=1&order=sacc');
        $response->assertStatus(422)->assertJsonFragment(["errors" => ["order" => ["The selected order is invalid."]], "message" => "Data is invalid"]);
    }

    public function testListNoFailedJob()
    {
        $paginator = new LengthAwarePaginator([], 12, 1);
        $mock = Mockery::mock(FailedJobRepository::class)->makePartial()
            ->shouldReceive([
                'allPaginated' => $paginator
            ])
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\FailedJobRepository', $mock);
        $response = $this->get('/api/failedJobs');
        $response->assertStatus(200)->assertJsonFragment($this->getJsonFragment());
    }

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
