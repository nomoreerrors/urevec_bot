<?php

namespace Feature\CommandsTests\Menu;

use App\Services\CONSTANTS;
use Database\Seeders\SimpleSeeder;
use App\Classes\Menu;
use Tests\TestCase;

class RefreshMethodTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setPrivateChatBotService();
    }

    /**
     * Test that refresh() method sets the correct adminId
     *
     * @return void
     */
    public function testRefreshSetsAdminId()
    {
        $result = $this->setValueToProtectedProperty("adminId", Menu::class);
        $this->assertNull($result);
        Menu::refresh();
        $result = $this->setValueToProtectedProperty("adminId", Menu::class);
        $this->assertEquals($this->admin->admin_id, $result);
    }

    /**
     * Test that refresh() method sets isMenuRefresh to true
     *
     * @return void
     */
    public function testRefreshSetsIsMenuRefresh()
    {
        $result = $this->setValueToProtectedProperty("isMenuRefresh", Menu::class);
        $this->assertFalse($result);

        Menu::refresh();

        $result = $this->setValueToProtectedProperty("isMenuRefresh", Menu::class);
        $this->assertTrue($result);
    }

    /**
     * Test that refresh() method does not setPrivateChatCommand if menu is empty
     *
     * @return void
     */
    public function testRefreshDoesNotSetPrivateChatCommandIfMenuIsEmpty()
    {
        $this->forgetBackMenuArray();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CONSTANTS::REFRESH_BACK_MENU_FAILED);
        Menu::refresh();
    }

    /**
     * Test that refresh() method setsPrivateChatCommand with the last menu item
     *
     * @return void
     */
    public function testRefreshSetsPrivateChatCommandWithLastMenuItem()
    {
        $this->setBackMenuArrayToCache(["menu_item_1", "menu_item_2", "menu_item_3"], $this->getBackMenuCacheKey());
        Menu::refresh();
        $this->assertEquals("menu_item_3", $this->botService->getPrivateChatCommand());
    }
}
