<?php

namespace Tests\Feature;

use App\Enums\MainMenuCmd;
use App\Enums\UnusualCharsFilterCmd;
use App\Enums\ResNewUsersCmd;
use App\Enums\BadWordsFilterCmd;
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
use Tests\TestCase;

class PrivateChatCommandCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        //Prepare one admin in database that attached to a few chats
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->chat = Chat::first();
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }

    /**
     * Tescase where user selected one of his chats, chat is set and last  command gets from cache and executed
     * in this case moderation settings menu buttons are sent
     * @return void
     */
    public function testChatIsSetAndLastCommandRememberedAndSelectModerationSettingsRepliesWithButtons(): void
    {
        //Mock that user was entered some command previously and it was saved to use after user selected the chat
        $lastCommand = MainMenuCmd::MODERATION_SETTINGS->value;
        Cache::put(CONSTANTS::CACHE_LAST_COMMAND . $this->admin->admin_id, $lastCommand);
        // Mock that user is pressed select chat button with one of the titles from his chats in database
        $title = $this->admin->chats->first()->chat_title;
        $this->data["message"]["text"] = $title;

        $this->prepareDependencies();

        new PrivateChatCommandCore();

        $this->assertEquals($this->chat->chat_title, $title);

        $sendMessageLog = $this->getTestLogFile();
        // Assert that when the chat was set the message to user with a selected chat title has been sent
        $this->assertStringContainsString("Selected chat: " . $title . "", $sendMessageLog);
        // Assert that a previously saved command was executed and moderation settings buttons were sent
        $this->assertStringContainsString(ResNewUsersCmd::SETTINGS->value, $sendMessageLog);
        $this->assertStringContainsString(MainMenuCmd::FILTERS_SETTINGS->value, $sendMessageLog);
        $this->assertStringContainsString(MainMenuCmd::MODERATION_SETTINGS->replyMessage(), $sendMessageLog);
    }


    public function testSelectFiltersSettingsReplyWithButtons()
    {
        $this->data["message"]["text"] = MainMenuCmd::FILTERS_SETTINGS->value;
        $this->prepareDependencies();
        // Fake that the chat was previously selected and it's id has been saved in cache
        $this->fakeChatSelected($this->admin->admin_id, $this->chat->chat_id);

        (new PrivateChatCommandCore());

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(MainMenuCmd::FILTERS_SETTINGS->replyMessage(), $sendMessageLog);
        $this->assertStringContainsString(UnusualCharsFilterCmd::SETTINGS->value, $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SETTINGS->value, $sendMessageLog);
    }




    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
        // Fake that chat was previously selected and saved in cache
        $this->fakeChatSelected($this->admin->admin_id, $this->chat->chat_id);
    }
}

