<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_send_message_return_status_true(): void
    {
        $testMessage = "His name is Robert Paulsen";
        $response = $this->service->sendMessage($testMessage);

        $this->assertTrue($response["ok"] === true);
    }
}
