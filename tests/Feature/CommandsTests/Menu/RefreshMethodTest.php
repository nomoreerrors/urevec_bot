<?php

namespace Feature\CommandsTests\Menu;

use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Cache;
use App\Classes\PrivateChatCommandCore;
use Database\Seeders\SimpleSeeder;
use App\Classes\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class RefreshMethodTest extends TestCase
{
    use MockBotService;
    use RefreshDatabase;

    protected $mockMenu;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);

        $this->mockBotCommand("random command");
        $this->fakeBotCommandHandlerCreate("private");
        $this->mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['getBackMenuFromCache'])
            ->getMock();
        // Cache::fake();
    }


    public function testRefreshSetsIsMenuRefreshFlagToTrue()
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
        // Cache::shouldReceive('get')->andReturn(null); // Mock the cache store
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CONSTANTS::REFRESH_BACK_MENU_FAILED);

        $this->mockMenu->setIsMenuRefresh(false);
        $this->assertFalse($this->mockMenu->getIsMenuRefresh());
        $this->expectGetBackMenuFromCache([]);
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

        // Expect will be called once
        $this->mockBotCommandHandler("private", null, $mockCommandHandler);
        // Expect with param
        $this->expectBotSetPrivateChatCommand("some menu 2");

        $this->mockMenu = $this->getMockMenu();
        $this->expectGetBackMenuFromCache(["some menu 2"]);

        // Don't know why, but it doesn't work without it
        $this->mockMenu->setIsMenuRefresh(false);

        $this->mockMenu->refresh();
        $this->assertTrue($this->mockMenu->getIsMenuRefresh());
    }


    public function testIsMenuRefreshFlagIsSetToFalse()
    {
        $this->mockBotService->expects($this->never())
            ->method('setPrivateChatCommand');
        $this->mockBotService->expects($this->never())
            ->method('commandHandler');

        $this->mockMenu = $this->getMockMenu();
        // Set the flag to true
        $this->mockMenu->setIsMenuRefresh(true);

        $this->mockMenu->refresh();
        $this->assertFalse($this->mockMenu->getIsMenuRefresh());
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

