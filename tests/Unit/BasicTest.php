<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    /**
     * exists documentation
     *
     * @return void
     */
    public function testDocumentation()
    {
        $response = $this->get('/docs/api-docs.json');
        $this->assertEquals(200, $response->status());
    }
}
