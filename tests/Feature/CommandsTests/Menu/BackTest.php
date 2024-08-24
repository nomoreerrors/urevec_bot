<?php

namespace Feature\CommandsTests;

use App\Classes\PrivateChatCommandCore;
use App\Exceptions\BaseTelegramBotException;
use App\Classes\NewUserRestrictionsCommand;
use App\Enums\ModerationSettingsEnum;
use App\Enums\NewUserRestrictionsEnum;
use Database\Seeders\SimpleSeeder;
use Illuminate\Database\Console\Migrations\BaseCommand;
use App\Classes\Menu;
use App\Services\TelegramBotService;
use App\Models\TelegramRequestModelBuilder;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

class BackTest extends TestCase
{
    use MockBotService;

    private const COMMANDS = ["first text", "second text", "third text", "fourth text"];

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
        $this->clearTestLogFile();
        $this->forgetBackMenuArray($this->admin->admin_id);
    }

    public function testCommandFromCacheIsCalledIfItExists()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $fakeCommandHandler = $this->createMock(PrivateChatCommandCore::class);
        $fakeCommandHandler->expects($this->once())
            ->method('handle');

        $this->mockBotCommandHandler("private", 1, $fakeCommandHandler);
        //Assert that the previous command from cache is called
        $this->expectBotSetPrivateChatCommand(self::COMMANDS[count(self::COMMANDS) - 2]);

        $menu = new Menu($this->mockBotService);
        $menu->back();
    }


    public function testBackThrowsExceptionWhenBackMenuArrayHasOnlyOneElement()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['getBackMenuFromCache'])
            ->getMock();

        $mockMenu->expects($this->once())
            ->method('getBackMenuFromCache')
            ->willReturn(["single command"]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No previous menu to go back to');

        $mockMenu->back();
    }

    public function testBackThrowsExceptionWhenBackMenuArrayIsEmpty()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['getBackMenuFromCache'])
            ->getMock();

        $mockMenu->expects($this->once())
            ->method('getBackMenuFromCache')
            ->willReturn([]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No previous menu to go back to');

        $mockMenu->back();
    }


    public function testCommandDeletedFromCacheAfterItWasCalled()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $fakeCommandHandler = $this->createMock(PrivateChatCommandCore::class);
        $this->mockBotCommandHandler("private", 1, $fakeCommandHandler);

        $menu = new Menu($this->mockBotService);
        $menu->back();

        $this->assertBackMenuArrayNotContains($this->admin->admin_id, self::COMMANDS[count(self::COMMANDS) - 1]);
    }


    public function testFailedToRetrieveLastBackMenuCommand()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['getLastBackMenu'])
            ->getMock();

        $mockMenu->expects($this->once())
            ->method('getLastBackMenu')
            ->willReturn("");

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to retrieve last back menu command');

        $mockMenu->back();
    }


    public function testFailedToSaveBackMenuToCache()
    {
        $this->setBackMenuArrayToCache(self::COMMANDS, $this->admin->admin_id);
        $this->mockBotCommand("random text");

        $mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['saveBackMenuToCache'])
            ->getMock();

        $mockMenu->expects($this->once())
            ->method('saveBackMenuToCache')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to save back menu to cache');

        $mockMenu->back();
    }

}

