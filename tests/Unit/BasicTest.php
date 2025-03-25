<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicTest extends TestCase
{
    public function testDocumentation()
    {
        $response = $this->get('/docs?api-docs.json');
        $this->assertEquals(200, $response->status());
    }
}
