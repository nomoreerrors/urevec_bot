<?php

namespace Tests\Feature;

use App\Classes\ChatSelector;
use App\Enums\ModerationSettingsEnum;
use App\Enums\UnusualCharsFilterEnum;
use Tests\Feature\Traits\MockBotService;
use App\Enums\NewUserRestrictionsEnum;
use App\Classes\Buttons;
use App\Classes\Menu;
use App\Enums\BadWordsFilterEnum;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\ResTime;
use App\Models\UnusualCharsFilter;
use App\Services\BotErrorNotificationService;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Classes\PrivateChatCommandCore;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\Feature\Traits\MockMenu;
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


    public function testUserIsSelectedModerationSettingsAfterChatIsSelected(): void
    {
        //Mock that the chat is previously selected 
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);

        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::SETTINGS->replyMessage(),
            (new Buttons())->getModerationSettingsButtons()
        );


        $this->mockBotCommand(ModerationSettingsEnum::SETTINGS->value);
        $this->mockBotGetChatMethod($this->admin->chats->first());
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("save", 1);
        $this->mockBotMenuCreate();

        $this->mockBotService->commandHandler()->handle();
    }

    public function testIfChatNotSelectedCommandWillBeSavedToBackMenuArrayAndRepliesWithSelectChatMenuButtons(): void
    {
        $this->expectReplyMessageWillBeSent(
            ModerationSettingsEnum::SELECT_CHAT->replyMessage(),
            $this->getAdminChatsButtons($this->admin)
        );

        $this->mockBotCommand(ModerationSettingsEnum::SETTINGS->value);
        $this->mockBotChatSelector();

        $this->expectMockBotMenuMethodWillBeCalled("save", 1);
        $this->mockBotMenuCreate();
        // Run command
        $this->mockBotService->commandHandler()->handle();
    }

    public function testSelectingtFiltersSettingsRepliesWithMenuButtons()
    {
        //Mock that the chat is previously selected 
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);
        $buttons = $this->getFiltersSettingsButtons();

        $this->assertTrue(in_array(BadWordsFilterEnum::SETTINGS->value, $buttons));
        $this->assertTrue(in_array(UnusualCharsFilterEnum::SETTINGS->value, $buttons));

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
    public function testUserIsSelectedChatRepliesWithSelectedChatTitle()
    {
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

