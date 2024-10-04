<?php

namespace Feature\Feature;

use App\Enums\CommandEnums\ModerationSettingsEnum;
use App\Models\Chat;
use App\Services\TelegramBotService;
use App\Classes\Buttons;
use Tests\Feature\Traits\MockBotService;
use App\Enums\CommandEnums\MainMenuEnum;
use App\Models\Admin;
use Database\Seeders\SimpleSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureModerationSettingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }


    /**
     * Feature test to test that /moderation_settings command from MainMenuCommand.php
     * works correctly and sends the right message with moderation settings buttons.
     * It tests that command without any problems and sends the right message.
     * @return void
     */
    public function testSendModerationSettingsCommandWhenChatExistsInDatabase()
    {
        // Create chat with one admin to avoid creating new one
        (new SimpleSeeder())->attachAdmins(1);
        $admin = Admin::first();

        // $j = $admin->chats()->count();
        // Get buttons that should be sent in message to compare with sendMessage arguments
        $keyBoard = (new Buttons())->create(ModerationSettingsEnum::getValues(), 1, true);

        // Mock sendMessage method to make sure it is called and everything is ok
        $mockBotService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['sendMessage'])
            ->getMock();
        // Expect message with moderation settings buttons but the method code should be mocked 
        $mockBotService->expects($this->once())->method('sendMessage')
            ->with(MainMenuEnum::MODERATION_SETTINGS->replyMessage(), $keyBoard);


        // Put mockBotService in container so that TelegramApiMiddleware can use it instead of the real one
        app()->instance(TelegramBotService::class, $mockBotService);

        //Send command
        $data = $this->getPrivateChatMessage($admin->admin_id, '/moderation_settings');
        $this->post('/api/webhook', $data);
    }

}
