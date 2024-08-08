<?php

namespace Tests\Feature;

use App\Enums\ResNewUsersCmd;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\ResTime;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Services\PrivateChatCommandCore;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class PrivateChatCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $data;
    private $model;
    private $botService;
    private $chat;
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        //Prepare one admin in database that attached to a few chats
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->data = $this->getTextMessageModelData();
        // Assign fake admin id to correctly set $admin property in PrivateChatCommandService
        $this->data["message"]["from"]["id"] = $this->admin->admin_id;
        $this->data["message"]["chat"]["id"] = $this->admin->admin_id;
        //Prepare fake admins ids so that ModelBuilder can get them instead of calling api
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
    }

    public function testSetGroupTitles()
    {
        $this->prepareDependencies();
        $chatTitle = Chat::first()->chat_title;
        $commandsService = new PrivateChatCommandCore();
        $titles = $commandsService->getGroupsTitles();
        $this->assertContains($chatTitle, $titles);
    }

    public function testAdminDoesNotExistsInDatabaseOrPropertyisNotSetThrowsException(): void
    {
        $this->prepareDependencies();
        $this->admin->delete();
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::USER_NOT_ALLOWED);
        (new PrivateChatCommandCore());
    }

    /**
     * Tescase where user selected one of his chats, chat is set and last  command gets from cache and executed
     * @return void
     */
    public function testIfUserPressSelectChatButtonChatIsSetAndLastCommandGetsExecuted(): void
    {
        //Mock that user was entered some command previously and it was saved to use after user selected the chat
        $lastCommand = "/moderation_settings";
        Cache::put(CONSTANTS::CACHE_LAST_COMMAND . $this->admin->admin_id, $lastCommand);
        // Mock that user is pressed select chat button with one of the titles from his chats in database
        $title = $this->admin->chats->first()->chat_title;
        // Mock request with the title from one of the user's chats
        $this->data["message"]["text"] = "/" . $title;
        // Make request model and bot service to be used in PrivateChatCommandService
        $this->prepareDependencies();

        new PrivateChatCommandCore();
        $this->chat = $this->botService->getChat();
        $this->assertInstanceOf(Chat::class, $this->chat);
        $this->assertEquals($this->chat->chat_title, $title);

        $sendMessageLog = $this->getTestLogFile();
        // Assert that when the chat was set the message to user with a selected chat title has been sent
        $this->assertStringContainsString("Selected chat: " . $title . "", $sendMessageLog);
        // Assert that a previously saved command was executed and moderation settings buttons were sent
        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::FILTER_SETTINGS_CMD, $sendMessageLog);
        $this->clearTestLogFile();
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SETTINGS->value;
        $this->prepareDependencies();
        // Fake that the chat was previously selected and it's id has been saved in cache
        Cache::put("last_selected_chat_" . $this->model->getChatId(), $this->chat->chat_id);
        (new PrivateChatCommandCore());


        $canSendMessages = $this->chat->newUserRestrictions->can_send_messages;
        $canSendMedia = $this->chat->newUserRestrictions->can_send_media;
        $restrictNewUsers = $this->chat->newUserRestrictions->restrict_new_users;


        $canSendMessages = $canSendMessages === 1 ? ResNewUsersCmd::DISABLE_SEND_MESSAGES->value : ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $canSendMedia = $canSendMedia === 1 ? ResNewUsersCmd::DISABLE_SEND_MEDIA->value : ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $restrictNewUsers = $restrictNewUsers === 1 ? ResNewUsersCmd::DISABLE_ALL->value : ResNewUsersCmd::ENABLE_ALL->value;

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString($canSendMessages, $sendMessageLog);
        $this->assertStringContainsString($canSendMedia, $sendMessageLog);
        $this->assertStringContainsString($restrictNewUsers, $sendMessageLog);
        $this->clearTestLogFile();
    }


    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SELECT_TIME->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);
        //Fake that chat was previously selected and it's id has been saved in cache
        Cache::put("last_selected_chat_" . $this->model->getChatId(), $this->chat->chat_id);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_DAY->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_TWO_HOURS->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_WEEK->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_MONTH->value, $sendMessageLog);
        $this->clearTestLogFile();
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SET_TIME_MONTH->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 before test
        $this->setAllRestrictionsToFalse($this->chat);
        $this->assertEquals(0, $this->chat->newUserRestrictions->restrict_new_users);


        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(ResTime::MONTH->value, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_MONTH->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_ALL->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);
        $this->chat->newUserRestrictions()->update(['restriction_time' => ResTime::DAY->value]);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_messages);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertEquals(ResTime::DAY->value, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_ALL->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }


    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_ALL->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_ALL->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }


    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }
}

