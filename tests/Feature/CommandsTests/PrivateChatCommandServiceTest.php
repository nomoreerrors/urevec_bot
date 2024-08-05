<?php

namespace Tests\Feature;

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
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        // PrivateChatCommandService dependencies   
        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }

    public function testSetGroupTitles()
    {
        $chatTitle = Chat::first()->chat_title;
        $commandsService = new PrivateChatCommandService();
        $titles = $commandsService->getGroupsTitles();
        $this->assertContains($chatTitle, $titles);
    }

    public function testAdminDoesNotExistsInDatabaseOrPropertyisNotSetThrowsException(): void
    {
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
        // Just to avoid multiple calls to api:
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        // Make request model and bot service to be used in PrivateChatCommandService
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        app()->instance("requestModel", $this->model);
        $chat = (new PrivateChatCommandService())->getSelectedChat();
        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertEquals($chat->chat_title, $title);

        $sendMessageLog = file_get_contents(storage_path("logs/testing.log"));
        // Assert that when the chat was set the message to user with a selected chat title has been sent
        $this->assertStringContainsString("Selected chat: " . $title . "", $sendMessageLog);
        // Assert that a previously saved command was executed and moderation settings buttons were sent
        $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::FILTER_SETTINGS_CMD, $sendMessageLog);

        unlink(storage_path("logs/testing.log"));
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $chat = Chat::first();
        $this->data["message"]["text"] = CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD;
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        new TelegramBotService($this->model);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
        // Fake that the chat was previously selected and it's id has been saved in cache
        Cache::put("last_selected_chat_" . $this->model->getChatId(), $chat->chat_id);

        (new PrivateChatCommandService());

        $sendMessageLog = file_get_contents(storage_path("logs/testing.log"));


        $this->assertStringContainsString(CONSTANTS::RESTRICT_MESSAGES_FOR_NEW_USERS_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::RESTRICT_MEDIA_FOR_NEW_USERS_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD, $sendMessageLog);
        $this->assertStringContainsString(CONSTANTS::REPLY_RESTRICT_SELECT_RESTRICTIONS_FOR_NEW_USERS, $sendMessageLog);

        unlink(storage_path("logs/testing.log"));
    }


    //TODO fix
    // public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    // {
    //     $chat = Chat::first();
    //     $this->data["message"]["text"] = CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD;
    //     $this->model = (new TelegramRequestModelBuilder($this->data))->create();
    //     $botService = new TelegramBotService($this->model);
    //     $botService->setChat($chat->chat_id);
    //     $this->assertNotNull($botService->getChat());

    //     app()->instance("requestModel", $this->model);
    //     app()->instance("botService", $this->botService);

    //     (new PrivateChatCommandService());
    //     $sendMessageLog = file_get_contents(storage_path("logs/testing.log"));

    //     $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD, $sendMessageLog);
    //     $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD, $sendMessageLog);
    //     $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD, $sendMessageLog);
    //     $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD, $sendMessageLog);
    //     unlink(storage_path("logs/testing.log"));
    // }


    // public function testUpdateNewUsersRestrictionsTime()
    // {
    //     $chat = Chat::first();
    //     $this->data["message"]["text"] = CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD;
    //     $this->model = (new TelegramRequestModelBuilder($this->data))->create();
    //     $botService = new TelegramBotService($this->model);
    //     $botService->setChat($chat->chat_id);
    //     // Asserting that the chat is currently set and there's no need to send select chat buttons to user
    //     //instead of executing the command
    //     $this->assertNotNull($botService->getChat());

    //     app()->instance("requestModel", $this->model);
    //     app()->instance("botService", $this->botService);
    //     $chat->newUserRestrictions()->update([
    //         'restrict_new_users' => 0,
    //         'restriction_time' => 0,
    //         'can_send_messages' => 0,
    //         'can_send_media' => 0
    //     ]);

    //     // $lol = $chat->newUserRestrictions()->first()->restrict_new_users;
    //     $this->assertEquals(0, $chat->newUserRestrictions->restrict_new_users);

    //     (new PrivateChatCommandService());

    //     $this->assertEquals(1, $chat->newUserRestrictions()->first()->restrict_new_users);
    //     $this->assertEquals(CONSTANTS::RESTIME_MONTH, $chat->newUserRestrictions()->first()->restriction_time);


    //     // $sendMessageLog = file_get_contents(storage_path("logs/testing.log"));

    //     // $this->assertStringContainsString(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD, $sendMessageLog);
    //     unlink(storage_path("logs/testing.log"));
    // }
}

