<?php

namespace Tests\Feature;

use App\Classes\CommandBuilder;
use App\Exceptions\SetCommandsFailedException;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\DB;
use App\Models\Eloquent\BotChat;
use App\Models\MessageModels\MediaModels\MultiMediaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Cache;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\App;
use App\Classes\CommandsList;

class SetMyCommandsTest extends TestCase
{
    use RefreshDatabase;
    protected $data;
    protected $chat;
    protected $chatId;
    protected $adminsIdsCacheKey;
    protected $adminsIdsArray;
    protected $model;
    protected $botService;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = $this->getMultiMediaModelData();
        $this->chatId = $this->data["message"]["chat"]["id"];
        // TelegramBotService needs commandsList instance to work
        // It's created in a TelegramApiMiddleware in a general workflow
        app()->instance("commandsList", new CommandsList());
        //Setting up admins ids array when creating model
        $this->model = new BaseTelegramRequestModel($this->data);
        $this->botService = new TelegramBotService($this->model);
        $this->chat = $this->botService->createChat();
    }

    /**
     * General testcase for setMyCommands function based on  a real request to API
     * @return void
     */
    public function testSetMyCommands(): void
    {
        // Make sure that RefreshDatabase trait works before test
        $this->botService->setMyCommands();

        $this->assertEquals([7400599756, 754429643], $this->chat->private_commands_access);
        $this->assertEquals('admins', $this->chat->group_commands_access);
        $this->assertEquals(1, $this->chat->my_commands_set);
    }

    /**
     *  Testcase where the setMyCommands function throws an exception if the response from the API fails.  
     * @return void
     */
    public function testSetGroupChatCommandsForAdminsFailedThrowsException(): void
    {
        $this->fakeFailedResponse();

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_GROUP_CHAT_COMMANDS_FAILED);

        $this->botService->setGroupChatCommandsForAdmins();
    }

    /**
     * Testcase where the setMyCommands function throws an exception if the response from the API fails.
     * @return void
     */
    public function testSetPrivateChatCommandsForAdminsIfFailedThrowsException(): void
    {
        $this->fakeFailedResponse();

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED);

        $this->botService->setPrivateChatCommandsForAdmins();
    }

    /**
     * Testcase where the setMyCommands function throws an exception if admins array is empty.
     * @return void
     */
    public function testSetPrivateChatCommandsForAdminsIfAdminsIdsEmptyThrowsException(): void
    {
        $this->chat->update([
            'chat_admins' => []
        ]);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED .
            CONSTANTS::EMPTY_ADMIN_IDS_ARRAY);

        $this->botService->setPrivateChatCommandsForAdmins();
    }

    /**
     * Testcase where checkifCommandsAreSet function returns static if commands set correctly  
     * @return void
     */
    public function testCheckIfCommandsAreSet(): void
    {
        $command = "test";
        $description = "description_test";
        $secondCommand = "test2";
        $secondDescription = "description_test2";

        $this->fakeGetMyCommandsResponse(
            $command,
            $description,
            $secondCommand,
            $secondDescription
        );

        $commands = (new CommandBuilder($this->getAdminId()))
            ->command($command, $description)
            ->command($secondCommand, $secondDescription)
            ->withChatScope()
            ->get();

        $this->assertInstanceOf(
            TelegramBotService::class,
            $this->botService->checkifCommandsAreSet($this->getAdminId(), $commands)
        );
    }

    public function testCheckIfCommandsAreSetThrowsException(): void
    {
        $command = "test";
        $description = "description_test";
        $secondCommand = "test2";
        $secondDescription = "description_test2";

        $this->fakeGetMyCommandsResponse(
            $command,
            $description,
            $secondCommand,
            $secondDescription
        );

        $commands = (new CommandBuilder($this->getAdminId()))
            ->command("test3", "description_test3")
            ->command("test4", "description_test4")
            ->withChatScope()
            ->get();

        $this->expectException(SetCommandsFailedException::class);

        $this->botService->checkifCommandsAreSet($this->getAdminId(), $commands);
    }
}