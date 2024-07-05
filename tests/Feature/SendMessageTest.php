<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Services\TelegramBotService;
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
        $message = (new BaseTelegramRequestModel($this->testObjects[0]));
        $service = new TelegramBotService($message);

        $testMessage = "His name is Robert Paulsen";
        $response = $service->sendMessage($testMessage);

        $this->assertTrue($response["ok"] === true);
    }
}
