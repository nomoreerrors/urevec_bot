<?php

namespace Tests\Feature;

use App\Classes\ChatSelector;
use App\Classes\Commands\MainMenuCommand;
use App\Enums\CommandEnums\MainMenuEnum;
use App\Enums\CommandEnums\ModerationSettingsEnum;
use App\Classes\PrivateChatCommandCore;
use App\Classes\Menu;
use App\Models\TelegramRequestModelBuilder;
use App\Services\TelegramBotService;
use Tests\Feature\Traits\MockBotService;
use App\Classes\Buttons;
use App\Models\Chat;
use App\Models\Admin;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MainMenuCommandTest extends TestCase
{
    use MockBotService;
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        // (new SimpleSeeder())->run(1, 5);
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats->first();
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotCommandHandler("private");
    }


    public function testModerationSettingsCommandReplyWithButtons(): void
    {
        $this->expectReplyMessage(
            MainMenuEnum::MODERATION_SETTINGS->replyMessage(),
            (new Buttons())->getModerationSettingsButtons()
        );


        $this->mockBotCommand(MainMenuEnum::MODERATION_SETTINGS->value);
        $this->mockBotGetChatMethod($this->admin->chats->first());
        // $this->mockBotChatSelector();

        $this->expectMockMenuMethod("save", 1);
        $this->mockBotMenuCreate();

        new MainMenuCommand($this->mockBotService);
    }


    public function testSelectingtFiltersSettingsRepliesWithMenuButtons()
    {
        $buttons = (new Buttons())->getFiltersSettingsButtons();

        $this->expectReplyMessage(
            ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage(),
            $buttons
        );

        $this->mockBotCommand(ModerationSettingsEnum::FILTERS_SETTINGS->value);
        $this->mockBotChatSelector();

        $this->expectMockMenuMethod("save", 1);
        $this->mockBotMenuCreate();
        // Run command
        new MainMenuCommand($this->mockBotService);
    }
}
