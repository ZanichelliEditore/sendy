<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $manager = app('cache');
        $mock = \Mockery::mock($manager)->makePartial();
        $mock->shouldReceive('driver')->andReturn($manager->driver());
        Cache::swap($mock);
    }
}
