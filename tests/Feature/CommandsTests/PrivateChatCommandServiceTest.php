<?php

namespace Tests\Feature;

use App\Enums\ResNewUsersCmd;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Services\PrivateChatCommandService;
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
        $commandsService = new PrivateChatCommandService();
        $titles = $commandsService->getGroupsTitles();
        $this->assertContains($chatTitle, $titles);
    }

    public function testAdminDoesNotExistsInDatabaseOrPropertyisNotSetThrowsException(): void
    {
        $this->prepareDependencies();
        $this->admin->delete();
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::USER_NOT_ALLOWED);
        (new PrivateChatCommandService());
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

        new PrivateChatCommandService();
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
        (new PrivateChatCommandService());


        $canSendMessages = $this->chat->newUserRestrictions->can_send_messages;
        $canSendMedia = $this->chat->newUserRestrictions->can_send_media;
        $restrictNewUsers = $this->chat->newUserRestrictions->restrict_new_users;


        $canSendMessages = $canSendMessages === 1 ? ResNewUsersCmd::DISABLE_SEND_MESSAGES->value : ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $canSendMedia = $canSendMedia === 1 ? ResNewUsersCmd::DISABLE_SEND_MESSAGES->value : ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $restrictNewUsers = $restrictNewUsers === 1 ? ResNewUsersCmd::DISABLE_ALL_RESTRICTIONS->value : ResNewUsersCmd::ENABLE_ALL_RESTRICTIONS->value;

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString($canSendMessages, $sendMessageLog);
        $this->assertStringContainsString($canSendMedia, $sendMessageLog);
        $this->assertStringContainsString($restrictNewUsers, $sendMessageLog);
        $this->clearTestLogFile();
    }


    //TODO Делаем этот тест

    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->data["message"]["text"] = CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);
        //Fake that chat was previously selected and it's id has been saved in cache
        Cache::put("last_selected_chat_" . $this->model->getChatId(), $this->chat->chat_id);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD, $sendMessageLog);
        $this->clearTestLogFile();
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->data["message"]["text"] = CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 to avoid any previous restrictions
        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 0,
            'restriction_time' => 0,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);

        $this->assertEquals(0, $this->chat->newUserRestrictions->restrict_new_users);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(CONSTANTS::RESTIME_MONTH, $this->chat->newUserRestrictions()->first()->restriction_time);
        $this->assertStringContainsString(CONSTANTS::REPLY_RESTRICT_NEW_USERS_FOR_MONTH, $sendMessageLog);
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

