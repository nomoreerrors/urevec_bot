<?php

namespace Feature\CommandsTests\Menu;

use App\Services\CONSTANTS;
use App\Classes\PrivateChatCommandCore;
use Database\Seeders\SimpleSeeder;
use App\Classes\Menu;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class RefreshMethodTest extends TestCase
{
    use MockBotService;

    protected $mockMenu;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);

        $this->mockBotCommand("random command");
        $this->fakeBotCommandHandlerCreate("private");

        $this->clearTestLogFile();
        $this->forgetBackMenuArray($this->admin->admin_id);
    }


    public function testRefreshSetsIsMenuRefreshToTrue()
    {
        $this->mockMenu = $this->getMockMenu();
        $this->expectGetBackMenuFromCache(["some menu"]);


        $this->assertFalse($this->mockMenu->getIsMenuRefresh());
        $this->mockMenu->refresh();
        $this->assertTrue($this->mockMenu->getIsMenuRefresh());
    }

    public function testThrowsExceptionWhenGetBackMenuFromCacheFails()
    {
        $this->mockMenu = $this->getMockMenu();
        $this->expectGetBackMenuFromCache(null);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CONSTANTS::REFRESH_BACK_MENU_FAILED);
        $this->mockMenu->refresh();
    }

    public function testRefreshScriptRunsSuccessfully()
    {
        $mockCommandHandler = $this->createMock(PrivateChatCommandCore::class);
        $mockCommandHandler->expects($this->once())
            ->method('handle');

        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotCommand("random command");

        $this->mockBotCommandHandler("private", null, $mockCommandHandler);
        $this->expectBotSetPrivateChatCommand("some menu");

        $this->mockMenu = $this->getMockMenu();
        $this->expectGetBackMenuFromCache(["some menu"]);


        $this->mockMenu->refresh();
    }


    private function getMockMenu()
    {
        return $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['getBackMenuFromCache'])
            ->getMock();
    }

    /**
     * Expectation for getBackMenuFromCache method
     * @param mixed $returnValue
     * @return void
     */
    private function expectGetBackMenuFromCache($returnValue)
    {
        $this->mockMenu->expects($this->once())
            ->method('getBackMenuFromCache')
            ->willReturn($returnValue);
    }


}

