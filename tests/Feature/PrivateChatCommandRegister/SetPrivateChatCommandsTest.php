<?php

namespace Tests\Feature;

use App\Classes\CommandBuilder;
use App\Classes\PrivateChatCommandRegister;
use App\Models\Admin;
use App\Models\MessageModels\TextMessageModel;
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

class SetPrivateChatCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }


    /**
     * @method setPrivateChatCommands
     * @return void
     */
    public function testEmptyCommandsArrayThrowsException(): void
    {
        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();


        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED);
        $privateChatCommandRegister->setPrivateChatCommands(123, []);
    }

    /**
     * @method setPrivateChatCommands
     */
    public function testExpectedCommandsArrayWillBeSent(): void
    {
        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($this->createMock(Chat::class));

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods([])
            ->getMock();


        $command = [
            "command" => "/test_command",
            "description" => "test description",
        ];

        $secondCommand = [
            "command" => "/second_test_command",
            "description" => "second test description",
        ];

        $thirdCommand = [
            "command" => "/third_test_command",
            "description" => "third test description",
        ];

        $expectedResult = [
            "commands" => [
                $command,
                $secondCommand,
                $thirdCommand
            ],
            "scope" => [
                "type" => "chat",
                "chat_id" => 123,
            ]
        ];

        $fakeResponse = $this->createMock(\Illuminate\Http\Client\Response::class);
        $fakeResponse->method('Ok')->willReturn(true);

        $botService->expects($this->once())
            ->method('sendPost')
            ->with('setMyCommands', $expectedResult)
            ->willReturn($fakeResponse);


        $privateChatCommandRegister->setPrivateChatCommands(
            123,
            [
                $command,
                $secondCommand,
                $thirdCommand
            ]
        );
    }


    /**
     * @method setPrivateChatCommands
     * @return void
     */
    public function testTrowsExceptionIfResponseIsNotOk(): void
    {
        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($this->createMock(Chat::class));

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods(['buildCommands']) // Will return null which will throw an exception
            ->getMock();

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(Constants::SET_PRIVATE_CHAT_COMMANDS_FAILED);
        $privateChatCommandRegister->setPrivateChatCommands(123, ['some command']);
    }
}