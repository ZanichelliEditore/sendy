<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicTest extends TestCase
{
    public function testScrambleRoute()
    {
        $response = $this->get('api/documentation');
        $response->assertStatus(200);
    }

    public function testScrambleJsonRoute(): void
    {
        $response = $this->get('docs/api.json');
        $response->assertStatus(200);
    }
}
