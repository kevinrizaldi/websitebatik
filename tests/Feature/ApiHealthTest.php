<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    public function test_api_health_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()->assertExactJson(['ok' => true]);
    }
}
