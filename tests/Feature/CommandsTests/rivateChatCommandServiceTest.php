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
        app()->instance("botService", $this->botService);

        $chat = (new PrivateChatCommandService())->getChat();
        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertEquals($chat->chat_title, $title);

        $sendMessageLog = file_get_contents(storage_path("logs/testing.log"));
        // Assert that when the chat was set the message to user with a selected chat title has been sent
        $this->assertStringContainsString("Selected chat: " . $title . "", $sendMessageLog);
        // Assert that a previously saved command was executed and moderation settings buttons were sent
        $this->assertStringContainsString(CONSTANTS::NEW_USERS_RESTRICT_SETTINGS_CMD, $sendMessageLog);

        unlink(storage_path("logs/testing.log"));
    }
}

