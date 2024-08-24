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

class SaveTest extends TestCase
{
    use MockBotService;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->mockBotCreate();
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotGetAdminMethod($this->admin);
        $this->clearTestLogFile();
        $this->forgetBackMenuArray($this->admin->admin_id);
    }


    public function testSaveMethodSavesMenuToBackMenuArrayIfArrayIsEmpty()
    {
        $this->assertEmpty($this->getBackMenuArray($this->admin->admin_id));

        $this->mockBotCommand("first text");
        $menu = new Menu($this->mockBotService);
        $menu->save();

        $this->assertJsonBackMenuArrayContains("first text", $this->admin->admin_id);
    }


    public function testSaveMethodSavesMenusInOrder()
    {
        $array = ["first text", "second text", "third text", "fourth text"];
        //Save values to back menu array
        foreach ($array as $value) {
            $this->updateBotCommand($value);
            $menu = new Menu($this->mockBotService);
            $menu->save();
        }

        $this->assertBackMenuArrayContains($this->admin->admin_id, $array);
    }

    /**
     * If array contains "back" value meaning that user wants to go back to previous menu
     * in that case menu shouldn't be saved to back menu array and "back" flag should be removed
     * and return value should be void
     * @return void
     */
    public function testSaveMethodInCaseArrayContainsBackValue()
    {
        $array = ["first text", "second text", "third text", "fourth text", "back"];
        $this->setBackMenuArrayToCache($array, $this->admin->admin_id);
        //Assert that array exists and contains "back" value
        $this->assertBackMenuArrayContains($this->admin->admin_id, $array);

        //try to save random value to back menu
        $this->mockBotCommand("random text");
        $menu = new Menu($this->mockBotService);
        $menu->save();

        array_pop($array);
        $this->assertBackMenuArrayContains($this->admin->admin_id, $array);
        //Assert that "back" value was deleted
        $this->assertFalse($this->assertBackMenuArrayNotContains($this->admin->admin_id, "back"));
        //Assert that random value is not in back menu
        $this->assertFalse($this->assertBackMenuArrayNotContains($this->admin->admin_id, "random text"));
    }

    public function testSaveMethodInCaseIsRefreshFlagEqualsTrue()
    {
        $array = ["first text", "second text", "third text", "fourth text"];
        $this->setBackMenuArrayToCache($array, $this->admin->admin_id);
        //try to save random value to back menu
        $this->mockBotCommand("random text");
        $menu = new Menu($this->mockBotService);
        $this->setValueToProtectedProperty("isMenuRefresh", $menu, true);
        $menu->save();
        //Assert that menu is not changed
        $this->assertBackMenuArrayNotContains($this->admin->admin_id, "random text");
        $this->assertBackMenuArrayContains($this->admin->admin_id, $array);
    }

    public function testSaveMethodThrowsExceptionWhenCacheFails()
    {
        $this->mockBotCommand("some command");
        $mockMenu = $this->getMockBuilder(Menu::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['saveBackMenuToCache'])
            ->getMock();
        $mockMenu->method('saveBackMenuToCache')
            ->willThrowException(new \Exception('Cache failed'));

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage('Error saving menu');
        $mockMenu->save();
    }


    private function updateBotCommand(string $value)
    {
        $this->mockBotRefresh();
        $this->mockBotGetAdminMethod($this->admin);
        $this->mockBotCommand($value);
    }

}
