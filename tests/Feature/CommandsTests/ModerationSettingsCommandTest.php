<?php

namespace Tests\Feature;

use App\Classes\ChatSelector;
use App\Classes\PrivateChatCommandCore;
use App\Classes\Menu;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\MainMenuEnum;
use App\Services\TelegramBotService;
use App\Enums\ModerationSettingsEnum;
use Tests\Feature\Traits\MockBotService;
use App\Classes\Buttons;
use App\Models\Chat;
use App\Models\Admin;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ModerationSettingsCommandTest extends TestCase
{
    use MockBotService;
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->chat = $this->admin->chats->first();
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotCommandHandler("private");
    }


    public function testModerationSettingsCommandReplyWithButtons(): void
    {
        //Mock that chat is previously selected 
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);

        $this->expectReplyMessageWillBeSent(
            MainMenuEnum::MODERATION_SETTINGS->replyMessage(),
            (new Buttons())->getModerationSettingsButtons()
        );


        $this->mockBotCommand(MainMenuEnum::MODERATION_SETTINGS->value);
        $this->mockBotGetChatMethod($this->admin->chats->first());
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("save", 1);
        $this->mockBotMenuCreate();

        $this->mockBotService->commandHandler()->handle();
    }

    public function testMenuIsSavedToBackMenuArrayUntilUserSelectsChat(): void
    {
        // Reply with select chat buttons instead of moderation settings buttons
        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::SELECT_CHAT->replyMessage(),
            $this->getAdminChatsButtons($this->admin)
        );

        $this->mockBotCommand(MainMenuEnum::MODERATION_SETTINGS->value);
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("save", 1);
        $this->mockBotMenuCreate();
        // Run command
        $this->mockBotService->commandHandler()->handle();
    }

    public function testSelectingtFiltersSettingsRepliesWithMenuButtons()
    {
        //Mock that chat is previously selected 
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);
        $buttons = (new Buttons())->getFiltersSettingsButtons();

        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage(),
            $buttons
        );

        $this->mockBotCommand(ModerationSettingsEnum::FILTERS_SETTINGS->value);
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("save", 1);
        $this->mockBotMenuCreate();
        // Run command
        $this->mockBotService->commandHandler()->handle();
    }

    /**
     * Test that user is noticed which chat is selected
     * @return void
     */
    public function testSelectingChatRepliesWithSelectedChatTitle()
    {
        $this->setBackMenuArrayToCache(["/moderation_settings"], $this->admin->admin_id);

        $title = $this->admin->chats->first()->chat_title;
        $this->mockBotCommand($title);
        $this->mockBotGetChatMethod($this->admin->chats->first());
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("back", 1);
        $this->mockBotMenuCreate();

        $this->expectReplyMessageWillBeSent($this->stringContains($title));
        $this->mockBotService->commandHandler()->handle();
    }

}

