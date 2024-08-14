<?php

namespace Tests\Feature;

use App\Enums\ModerationSettingsEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Enums\ResNewUsersEnum;
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
use Tests\TestCase;

class ModerationSettingsCommandTest extends TestCase
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
        $this->deleteSelectedChatFromCache($this->admin->admin_id);
        $this->putLastCommandToCache($this->admin->admin_id, ModerationSettingsEnum::MODERATION_SETTINGS->value);
        // Mock that user is pressed select chat button with one of the titles from his chats in database
        $title = $this->admin->chats->first()->chat_title;
        $this->setCommand($title);
        $this->prepareDependencies();

        new PrivateChatCommandCore();

        $this->assertReplyMessageSent("Selected chat: " . $title);
        $this->assertReplyMessageSent(ModerationSettingsEnum::MODERATION_SETTINGS->replyMessage());
        // Assert that buttons were sent and previously saved command was executed
        $this->assertButtonsWereSent([
            ResNewUsersEnum::SETTINGS->value,
            ModerationSettingsEnum::FILTERS_SETTINGS->value,
        ]);
        $this->assertBackMenuArrayContains(ModerationSettingsEnum::MODERATION_SETTINGS->value);
    }


    public function testSelectFiltersSettingsReplyWithButtons()
    {
        $this->setCommand(ModerationSettingsEnum::FILTERS_SETTINGS->value);
        $this->prepareDependencies();

        (new PrivateChatCommandCore());

        $this->assertReplyMessageSent(ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage());
        $this->assertButtonsWereSent([
            UnusualCharsFilterEnum::SETTINGS->value,
            BadWordsFilterEnum::SETTINGS->value
        ]);
        $this->assertBackMenuArrayContains(ModerationSettingsEnum::FILTERS_SETTINGS->value);
    }


    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->chat->chat_id);
    }
}

