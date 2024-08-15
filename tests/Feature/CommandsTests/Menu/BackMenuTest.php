<?php

namespace Feature\CommandsTests;

use App\Classes\PrivateChatCommandCore;
use App\Classes\RestrictNewUsersCommand;
use App\Enums\ModerationSettingsEnum;
use App\Enums\ResNewUsersEnum;
use Database\Seeders\SimpleSeeder;
use Illuminate\Database\Console\Migrations\BaseCommand;
use App\Classes\Menu;
use App\Services\TelegramBotService;
use App\Models\TelegramRequestModelBuilder;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

class BackMenuTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setPrivateChatBotService(1, 1);
        $this->clearTestLogFile();
    }

    /**
     * Adding back menu to cache and asserting that it is added
     * Than asserting that menu pointer is moved by analogy with stack pointer
     * And values removed from cache in reverse order.
     * Back menu is that is saving when user is pressed one of the menu's buttons 
     * and then menus returning in reverse order one by one as user press back button.
     * @param mixed $cacheKey
     * @param mixed $menuPointer
     * @return void
     */
    public function testGeneral()
    {
        $this->prepareDependencies();
        $this->forgetBackMenuArray();

        $this->rememberBackMenuTest();
        $this->getLastBackMenuFromCacheTest();
        $this->moveBackMenuPointerTest();
        $this->avoidBackMenuOverflowTest();
    }


    public function rememberBackMenuTest()
    {
        Menu::save("first text");
        Menu::save("second text");
        Menu::save("third text");
        Menu::save("fourth text");

        $backMenuArray = $this->getBackMenuArray();

        $this->assertTrue(
            in_array("first text", $backMenuArray) &&
            in_array("second text", $backMenuArray) &&
            in_array("third text", $backMenuArray) &&
            in_array("fourth text", $backMenuArray)
        );
    }

    /**
     * Assert that menu pointer indicates the last element in cache 
     * which means that returned value is the last element of an array 
     * @return void
     */
    public function getLastBackMenuFromCacheTest()
    {
        $menuPointer = $this->getAccessToProtectedMethod('getLastBackMenuFromCache', Menu::class);
        $this->assertEquals("third text", $menuPointer);
    }

    /**
     * Asserting that menu pointer is moved by analogy with stack pointer
     * and values removed from cache in reverse order
     * @param mixed $cacheKey
     * @return void
     */
    public function moveBackMenuPointerTest()
    {
        $this->getAccessToProtectedMethod('moveUpBackMenuPointer', Menu::class);
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(3, $backMenuArray);
        $this->assertEquals("third text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer', Menu::class);
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(2, $backMenuArray);
        $this->assertEquals("second text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer', Menu::class);
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(1, $backMenuArray);
        $this->assertEquals("first text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer', Menu::class);
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(0, $backMenuArray);
    }

    /**
     * Test that when the last element of a back menu array is getting from cache
     * and the open previous menu command gets executed, same command won't write to cache again
     * and the menu pointer moved to the updated position.
     * @param string $cacheKey
     * @return void
     */
    public function avoidBackMenuOverflowTest()
    {
        $backMenuArray = ["first text", "second text", "third text", "fourth text"];
        $this->setBackMenuArrayToCache($backMenuArray);

        Menu::save("fourth text");
        $this->assertCount(3, $this->getBackMenuArray());
    }



    /**
     * General public method back()
     * @return void
     */
    public function testBackMethod()
    {
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->admin->chats->first()->chat_id);
        $this->fakeSendMessageSucceedResponse();

        // Expect that the command will be saved to cache as previous menu
        $this->data["message"]["text"] = ResNewUsersEnum::SETTINGS->value;
        $this->prepareDependencies();
        $this->forgetBackMenuArray(); //Clear cache before test 
        new PrivateChatCommandCore();

        $result = $this->getAccessToProtectedMethod('getLastBackMenuFromCache', Menu::class);
        $this->assertEquals(ResNewUsersEnum::SETTINGS->value, $result);


        // Expect that the command will be saved to cache as previous menu array at  index 1
        $this->data["message"]["text"] = ResNewUsersEnum::SELECT_RESTRICTION_TIME->value;
        $this->prepareDependencies();
        new PrivateChatCommandCore();
        $this->assertCount(2, $this->getBackMenuArray());

        // Fake that a back button was pressed
        $this->data["message"]["text"] = ModerationSettingsEnum::BACK->value;
        $this->prepareDependencies();
        new PrivateChatCommandCore();
        //Asserting that the menu pointer moved to the previous position and command was removed from cache
        $result = $this->getAccessToProtectedMethod('getLastBackMenuFromCache', Menu::class);
        $this->assertEquals(ResNewUsersEnum::SETTINGS->value, $result);
        $this->assertStringContainsString(ResNewUsersEnum::SETTINGS->replyMessage(), $this->getTestLogFile());
    }





    public function prepareDependencies()
    {
        $requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($requestModel);
        app()->singleton("botService", fn() => $this->botService);
        app()->singleton("requestModel", fn() => $requestModel);
    }


    /**
     * Testcase where menu is currently refreshing but there's also save() method getting executed during the command execution
     * that is saving previous menu to cache, but in this case menu was saved in back menu array when the user has entered menu 
     *  and we test that menu is not saved to cache again when the same command arrived.
     *  Also test  that it won't move a stack pointer and delete previous menu from cache like it works when the back() method is called
     * @return void
     */
    public function testBackMenuNotSavingIfMenuIsCurrentlyRefreshing()
    {
        $this->setBackMenuArrayToCache([]);
        $this->getAccessToProtectedProperty('isMenuRefresh', Menu::class, true);

        Menu::save("first text");
        Menu::save("second text");
        Menu::save("third text");
        Menu::save("fourth text");
        $this->assertCount(0, $this->getBackMenuArray());
    }

}
