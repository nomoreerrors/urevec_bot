<?php

namespace Feature\CommandsTests;

use App\Classes\PrivateChatCommandCore;
use App\Classes\RestrictNewUsersCommand;
use App\Enums\MainMenuCmd;
use App\Enums\ResNewUsersEnum;
use Database\Seeders\SimpleSeeder;
use Illuminate\Database\Console\Migrations\BaseCommand;
use App\Classes\BackMenuButton;
use App\Services\TelegramBotService;
use App\Models\TelegramRequestModelBuilder;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

class BackMenuButtonClassTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run(1, 1);
        $this->admin = Admin::first();
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        $this->clearTestLogFile();
    }

    /**
     * Adding back menu to cache and asserting that it is added
     * Than asserting that menu pointer is moved by analogy with stack pointer
     * And values removed from cache in reverse order.
     * Back menu is that is saving when user is selecting one the menu's buttons in straight order
     * and then menu's returning in reverse order one by one as user pressed back button.
     * @param mixed $cacheKey
     * @param mixed $menuPointer
     * @return void
     */
    public function testGeneral()
    {
        $this->prepareDependencies();
        $cacheKey = $this->getCacheKey();
        $this->forgetBackMenuArray();

        $this->rememberBackMenuTest($cacheKey);
        $this->getLastBackMenuFromCacheTest();
        $this->moveBackMenuPointerTest($cacheKey);
        $this->avoidBackMenuOverflowTest($cacheKey);
    }


    public function rememberBackMenuTest(string $cacheKey)
    {
        BackMenuButton::rememberBackMenu("first text");
        BackMenuButton::rememberBackMenu("second text");
        BackMenuButton::rememberBackMenu("third text");
        BackMenuButton::rememberBackMenu("fourth text");

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
        $menuPointer = $this->getAccessToProtectedMethod('getLastBackMenuFromCache');
        $this->assertEquals("third text", $menuPointer);
    }

    /**
     * Asserting that menu pointer is moved by analogy with stack pointer
     * and values removed from cache in reverse order
     * @param mixed $cacheKey
     * @return void
     */
    public function moveBackMenuPointerTest($cacheKey)
    {
        $this->getAccessToProtectedMethod('moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(3, $backMenuArray);
        $this->assertEquals("third text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(2, $backMenuArray);
        $this->assertEquals("second text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(1, $backMenuArray);
        $this->assertEquals("first text", end($backMenuArray));

        $this->getAccessToProtectedMethod('moveUpBackMenuPointer');
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
    public function avoidBackMenuOverflowTest(string $cacheKey)
    {
        $backMenuArray = ["first text", "second text", "third text", "fourth text"];
        $this->setBackMenuArrayToCache($backMenuArray, $cacheKey);

        BackMenuButton::rememberBackMenu("fourth text");
        $this->assertCount(3, $this->getBackMenuArray());
    }

    /**
     * Get array from cache
     * @return array
     */
    protected function getBackMenuArray(): array
    {
        $cacheKey = "back_menu_" . $this->botService->getAdmin()->admin_id;
        return json_decode(Cache::get($cacheKey), true);
    }

    protected function setBackMenuArrayToCache(array $backMenuArray, string $cacheKey)
    {
        Cache::put($cacheKey, json_encode($backMenuArray));
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

        $result = $this->getAccessToProtectedMethod('getLastBackMenuFromCache');
        $this->assertEquals(ResNewUsersEnum::SETTINGS->value, $result);


        // Expect that the command will be saved to cache as previous menu array at  index 1
        $this->data["message"]["text"] = ResNewUsersEnum::SELECT_RESTRICTION_TIME->value;
        $this->prepareDependencies();
        new PrivateChatCommandCore();
        $this->assertCount(2, $this->getBackMenuArray());

        // Fake that a back button was pressed
        $this->data["message"]["text"] = MainMenuCmd::BACK->value;
        $this->prepareDependencies();
        new PrivateChatCommandCore();
        //Asserting that the menu pointer moved to the previous position and command was removed from cache
        $result = $this->getAccessToProtectedMethod('getLastBackMenuFromCache');
        $this->assertEquals(ResNewUsersEnum::SETTINGS->value, $result);
        $this->assertStringContainsString(ResNewUsersEnum::SETTINGS->replyMessage(), $this->getTestLogFile());
    }


    public function getAccessToProtectedMethod($method)
    {
        $myClass = new BackMenuButton();
        $reflection = new ReflectionClass($myClass);
        // Getting a protected method
        $method = $reflection->getMethod($method);

        $method->setAccessible(true);
        $result = $method->invoke($myClass);
        return $result;
    }

    public function getCacheKey(): string
    {
        return "back_menu_" . $this->botService->getAdmin()->admin_id;
    }

    public function prepareDependencies()
    {
        $requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($requestModel);
        app()->singleton("botService", fn() => $this->botService);
        app()->singleton("requestModel", fn() => $requestModel);
    }

    public function forgetBackMenuArray()
    {
        $cacheKey = $this->getCacheKey();
        Cache::forget($cacheKey);
    }

}
