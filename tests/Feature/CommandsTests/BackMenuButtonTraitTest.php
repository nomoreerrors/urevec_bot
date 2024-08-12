<?php

namespace Feature\CommandsTests;

use App\Classes\RestrictNewUsersCommand;
use Database\Seeders\SimpleSeeder;
use Illuminate\Database\Console\Migrations\BaseCommand;
use App\Services\TelegramBotService;
use App\Models\TelegramRequestModelBuilder;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

class BackMenuButtonTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run(1, 1);
        $this->admin = Admin::first();

        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        $requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($requestModel);
        $this->botService->setChat($this->admin->chats->first()->chat_id);

        app()->singleton("requestModel", fn() => $requestModel);
        app()->singleton("botService", fn() => $this->botService);

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
    public function testBackMenu()
    {
        $cacheKey = $this->getCacheKey();
        Cache::forget($cacheKey);

        $this->rememberBackMenuTest($cacheKey);
        $this->getLastBackMenuFromCacheTest();
        $this->moveBackMenuPointerTest($cacheKey);
        $this->avoidBackMenuOverflowTest($cacheKey);
    }


    public function rememberBackMenuTest($cacheKey)
    {
        $this->getAccessToProtectedMethod("first text", 'rememberBackMenu');
        $this->getAccessToProtectedMethod("second text", 'rememberBackMenu');
        $this->getAccessToProtectedMethod("third text", 'rememberBackMenu');
        $this->getAccessToProtectedMethod("fourth text", 'rememberBackMenu');

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
        $menuPointer = $this->getAccessToProtectedMethod("random text", 'getLastBackMenuFromCache');
        $this->assertEquals("fourth text", $menuPointer);
    }

    /**
     * Asserting that menu pointer is moved by analogy with stack pointer
     * and values removed from cache in reverse order
     * @param mixed $cacheKey
     * @return void
     */
    public function moveBackMenuPointerTest($cacheKey)
    {
        $this->getAccessToProtectedMethod("random text", 'moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(3, $backMenuArray);
        $this->assertEquals("third text", end($backMenuArray));

        $this->getAccessToProtectedMethod("random text", 'moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(2, $backMenuArray);
        $this->assertEquals("second text", end($backMenuArray));

        $this->getAccessToProtectedMethod("random text", 'moveUpBackMenuPointer');
        $backMenuArray = $this->getBackMenuArray();
        $this->assertCount(1, $backMenuArray);
        $this->assertEquals("first text", end($backMenuArray));

        $this->getAccessToProtectedMethod("random text", 'moveUpBackMenuPointer');
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
        $this->getAccessToProtectedMethod("fourth text", 'rememberBackMenu');
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


    public function getAccessToProtectedMethod(string $command, string $method)
    {
        $myClass = new RestrictNewUsersCommand($command);
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

}
