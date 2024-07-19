<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SendMessageTest extends TestCase
{

    public function testSendMessageReturnStatusTrue(): void
    {
        $messageModel = $this->getMessageModel();
        $service = new TelegramBotService($messageModel);

        $testMessage = "His name is Robert Paulsen";
        $response = $service->sendMessage($testMessage);

        $this->assertTrue($response["ok"]);
    }
}
