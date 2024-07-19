<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Models\MessageModel;
use App\Models\TextMessageModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;

class DeleteMessageTest extends TestCase
{
    /**
     * Test that a message deletion request with a non-existent message ID returns false.
     * @return void
     */
    public function test_deleting_nonexistent_message_returns_false(): void
    {
        $data = $this->getMessageModel()->getData();
        $data['message']['message_id'] = 9999999;

        $model = new BaseTelegramRequestModel($data);
        $model->getModel();

        $botService = new TelegramBotService($model);
        $this->assertFalse($botService->deleteMessage());

    }


}
