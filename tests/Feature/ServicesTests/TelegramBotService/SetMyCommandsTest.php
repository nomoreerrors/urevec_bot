<?php

namespace Tests\Feature;

use App\Classes\CommandBuilder;
use App\Models\Admin;
use Database\Seeders\SimpleSeeder;
use App\Exceptions\SetCommandsFailedException;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\DB;
use App\Models\Chat;
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
        // Getting a multimedia model data just for various testcases
        // with a real chat id to successfully set up commands for real admins
        $this->data = $this->getMultiMediaModelData();
        $this->chatId = $this->data["message"]["chat"]["id"];
        // Setting up commandsList instance for TelegramBotService's constructor
        app()->instance("commandsList", new CommandsList());
        //Setting up admins ids array 
        $this->model = new TelegramRequestModelBuilder($this->data);
        // Using admins ids from model to create a new chat and attaching admins
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
        foreach ($this->chat->admins as $admin) {
            $this->assertEquals(1, $admin->pivot->my_commands_set);
            $this->assertEquals(1, $admin->pivot->group_commands_access);
            $this->assertEquals(1, $admin->pivot->private_commands_access);
        }
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

    public function testCheckIfSetCommandsFailedThrowsException(): void
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