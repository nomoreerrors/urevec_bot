<?php

namespace Tests\Feature\ServicesTests\TelegramBotService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\BaseTelegramRequestModel;
use App\Services\TelegramBotService;
use App\Classes\CommandsList;

class CreateChatTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateChat(): void
    {
        $data = $this->getMessageModelData();
        $requesModel = (new BaseTelegramRequestModel($data))->getModel();
        $botService = new TelegramBotService($requesModel);
        $chat = $botService->createChat();

        $this->assertInstanceOf(\App\Models\Eloquent\BotChat::class, $chat);
        $this->assertDatabaseHas('bot_chats', ['chat_id' => $chat->chat_id]);
    }
}
