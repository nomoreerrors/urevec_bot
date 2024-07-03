<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;

class SendMessageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_send_message_return_status_true(): void
    {

        $message = new TelegramMessageModel($this->testObjects[0]);
        $service = new TelegramBotService($message);

        $testMessage = "His name is Robert Paulsen";
        $response = $service->sendMessage($testMessage);

        $this->assertTrue($response["ok"] === true);
    }
}
