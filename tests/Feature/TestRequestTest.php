<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TestRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_request(): void
    {
        $data = $this->getMessageModelData();
        $data["message"]["from"]["id"] = 214543;

        $data = $this->getMessageModelData();
        $response = $this->post('/api/webhook', $data);

        // $response->assertStatus(200);
    }
}
