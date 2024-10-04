<?php

namespace Feature\Feature\SelectChat;
use App\Classes\Buttons;
use App\Enums\CommandEnums\ModerationSettingsEnum;
use App\Services\CONSTANTS;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\SimpleSeeder;
use App\Models\Admin;

class FeutureSelectChatCommandTest extends TestCase
{
    use RefreshDatabase;
    public function testSelectChatCommandWhenAdminHasMultipleChats()
    {
        // Create chat with one admin to avoid creating new one
        $admin = $this->setAdminWithMultipleChats(2);
        $admin = Admin::first();

        // Get buttons that should be sent in message to compare with sendMessage arguments
        $keyBoard = (new Buttons())->getSelectChatButtons($admin->chats()->pluck('chat_title')->toArray());


        // Mock sendMessage method to make sure it is called and everything is ok
        $mockBotService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['sendMessage', 'createCommand'])
            ->getMock();

        //Expect that buttons will be sent and the script will stop before createCommand is called
        $mockBotService->expects($this->never())->method('createCommand');

        // // Expect message with moderation settings buttons but the method code should be mocked 
        $mockBotService->expects($this->once())->method('sendMessage')
            ->with(ModerationSettingsEnum::SELECT_CHAT->replyMessage(), $keyBoard);


        // Put mockBotService in container so that TelegramApiMiddleware can use it instead of the real one
        app()->instance(TelegramBotService::class, $mockBotService);

        //Send command
        $data = $this->getPrivateChatMessage($admin->admin_id, 'Выбрать чат');
        $this->post('/api/webhook', $data);
    }


    public function testSelectChatCommandWhenAdminHasOneChat()
    {
        // Create chat with one admin to avoid creating new one
        $admin = $this->setAdminWithMultipleChats(1);
        $admin = Admin::first();

        // Get buttons that should be sent in message to compare with sendMessage arguments
        $keyBoard = (new Buttons())->getSelectChatButtons($admin->chats()->pluck('chat_title')->toArray());


        // Mock sendMessage method to make sure it is called and everything is ok
        $mockBotService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['sendMessage', 'createCommand'])
            ->getMock();

        //Expect that buttons will be sent and the script will stop before createCommand is called
        $mockBotService->expects($this->never())->method('createCommand');

        $mockBotService->expects($this->once())->method('sendMessage')
            ->with(CONSTANTS::REPLY_ONLY_ONE_CHAT_AVAILABLE);


        // Put mockBotService in container so that TelegramApiMiddleware can use it instead of the real one
        app()->instance(TelegramBotService::class, $mockBotService);

        //Send command
        $data = $this->getPrivateChatMessage($admin->admin_id, 'Выбрать чат');
        $this->post('/api/webhook', $data);
    }
}

