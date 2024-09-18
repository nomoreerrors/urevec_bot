<?php

namespace Feature\PrivateChatCommandRegister;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\TelegramBotService;
use App\Exceptions\BaseTelegramBotException;
use App\Services\CONSTANTS;
use App\Classes\PrivateChatCommandRegister;

class SetMyCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testEmptyCommandsArrayThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $privateChatCommandRegister->setMyCommands(123, []);
    }

    public function testEmptyDescriptionThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $privateChatCommandRegister->setMyCommands(123, ['commands' => 'test']);
    }


    public function testEmptyCommandsKeyThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $privateChatCommandRegister->setMyCommands(123, ['description' => 'test']);
    }

    public function testMethodsExpectedToBeCalledWithExpectedParameters(): void
    {
        $adminId = 123;
        $command = ['commands' => 'test', 'description' => 'test'];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'updatePrivateChatCommandsAccessColumn',
                'updateMyCommandsColumn',
                'getMyCommands'
            ])
            ->getMock();

        $privateChatCommandRegister->expects($this->once())
            ->method('setPrivateChatCommands')
            ->with($adminId, $command);

        $privateChatCommandRegister->expects($this->once())
            ->method('getMyCommands')
            ->with('chat', $adminId)
            ->willReturn([]);

        $privateChatCommandRegister->expects($this->once())
            ->method('checkifCommandsAreSet')
            ->with($command, []);

        $privateChatCommandRegister->expects($this->once())
            ->method('updatePrivateChatCommandsAccessColumn');

        $privateChatCommandRegister->expects($this->once())
            ->method('updateMyCommandsColumn')
            ->with($adminId);


        $privateChatCommandRegister->setMyCommands($adminId, $command);
    }


    public function testMyCommandsColumnValueSetToTrue(): void
    {
        $admin = $this->setAdminWithMultipleChats(1);

        $chat = $admin->chats->first();
        $chat->admins()->update(['my_commands_set' => 0]);
        // $j = $chat->admins()->first()->pivot->my_commands_set;


        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($chat);


        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods([
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'updatePrivateChatCommandsAccessColumn',
                'getMyCommands'
            ])
            ->getMock();


        $privateChatCommandRegister->setMyCommands($admin->admin_id, ['commands' => 'test', 'description' => 'test']);
        $this->assertEquals(1, $chat->admins()->first()->pivot->my_commands_set);
    }

    /**
     * @method setPrivateChatCommands
     */
    public function testupdatePrivateChatCommandsAccessColumnSetToTrue(): void
    {
        $admin = $this->setAdminWithMultipleChats(1);

        $chat = $admin->chats->first();
        $chat->admins()->update(['private_commands_access' => 0]);


        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($chat);


        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods([
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'updateMyCommandsColumn',
                'getMyCommands'
            ])
            ->getMock();


        $privateChatCommandRegister->setMyCommands($admin->admin_id, ['commands' => 'test', 'description' => 'test']);
        $this->assertEquals(1, $chat->admins()->first()->pivot->private_commands_access);
    }

}
