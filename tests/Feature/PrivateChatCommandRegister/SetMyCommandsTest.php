<?php

namespace Feature\PrivateChatCommandRegister;

use App\Models\Admin;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
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

        $privateChatCommandRegister->setMyCommands(123, [
            ['command' => 'test']
        ]);
    }


    public function testEmptyCommandsKeyThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $privateChatCommandRegister->setMyCommands(123, [
            ['description' => 'test']
        ]);
    }

    public function testMethodsExpectedToBeCalledWithExpectedParameters(): void
    {
        $adminId = 123;
        $commands = [
            ['command' => 'test', 'description' => 'test']
        ];

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'validateCommands',
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'updatePrivateChatCommandsAccessColumn',
                'updateMyCommandsColumn',
                'getMyCommands'
            ])
            ->getMock();


        $privateChatCommandRegister->expects($this->once())
            ->method('validateCommands')
            ->with($commands);

        $privateChatCommandRegister->expects($this->once())
            ->method('setPrivateChatCommands')
            ->with($adminId, $commands);

        $privateChatCommandRegister->expects($this->once())
            ->method('getMyCommands')
            ->with('chat', $adminId)
            ->willReturn([]);

        $privateChatCommandRegister->expects($this->once())
            ->method('checkifCommandsAreSet')
            ->with($commands, []);

        $privateChatCommandRegister->expects($this->once())
            ->method('updatePrivateChatCommandsAccessColumn');

        $privateChatCommandRegister->expects($this->once())
            ->method('updateMyCommandsColumn')
            ->with($adminId);


        $privateChatCommandRegister->setMyCommands($adminId, $commands);
    }


    public function testMyCommandsColumnValueSetToTrueForAllAdmins(): void
    {
        // Set two admins with the same chat 
        (new SimpleSeeder())->attachAdmins(2);

        $chat = Chat::first();
        $admin = Admin::first();
        $secondAdmin = Admin::where('admin_id', '!=', $admin->admin_id)->first();

        //Set all admin commands to false
        $chat->admins()->update(['my_commands_set' => 0]);


        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($chat);


        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods([
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'getMyCommands',
                'validateCommands',
                'updatePrivateChatCommandsAccessColumn'
            ])
            ->getMock();


        $privateChatCommandRegister->setMyCommands($admin->admin_id, [
            ['command' => 'test', 'description' => 'test']
        ]);

        $privateChatCommandRegister->setMyCommands($secondAdmin->admin_id, [
            ['command' => 'test', 'description' => 'test']
        ]);

        $admins = $chat->admins()->get();
        $this->assertEquals(2, $admins->count());

        foreach ($admins as $admin) {
            $this->assertEquals(1, $admin->pivot->my_commands_set);
        }
    }

    public function testPrivateChatCommandsAccessColumnValueSetToTrueForAllAdmins(): void
    {
        // Set two admins with the same chat 
        (new SimpleSeeder())->attachAdmins(2);

        $chat = Chat::first();
        $admin = Admin::first();
        $secondAdmin = Admin::where('admin_id', '!=', $admin->admin_id)->first();

        //Set all admin commands to false
        $chat->admins()->update(['private_commands_access' => 0]);


        $botService = $this->createMock(TelegramBotService::class);
        $botService->method('getChat')->willReturn($chat);


        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$botService])
            ->onlyMethods([
                'setPrivateChatCommands',
                'checkifCommandsAreSet',
                'getMyCommands',
                'updateMyCommandsColumn',
                'validateCommands'
            ])
            ->getMock();


        $privateChatCommandRegister->setMyCommands($admin->admin_id, [
            ['command' => 'test', 'description' => 'test']
        ]);

        $privateChatCommandRegister->setMyCommands($secondAdmin->admin_id, [
            ['command' => 'test', 'description' => 'test']
        ]);

        $admins = $chat->admins()->get();

        $this->assertEquals(2, $admins->count());

        foreach ($admins as $admin) {
            $this->assertEquals(1, $admin->pivot->private_commands_access);
        }
    }


    // public function testPrivateChatCommandsAccessColumnUpdateFailedThrowsException(): void
    // {
    //     //
    // }


    // public function testSetMyCommandsFailedThrowsException(): void
    // {
    //     //
    // }

}
