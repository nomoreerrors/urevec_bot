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
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->clearTestLogFile();
        $this->forgetBackMenuArray($this->admin->admin_id);
    }


    public function testRememberBackMenu()
    {
        $array = ["first text", "second text", "third text", "fourth text"];
        $this->saveValuesToBackMenuArray($array);
        $this->assertBackMenuArrayHas($array);
    }

    public function testBackMethod()
    {
        $array = ["first text", "second text", "third text", "fourth text"];
        $this->saveValuesToBackMenuArray($array);

        $mockBotService = $this->createMock(TelegramBotService::class);
        $this->prepareMenuConstructDeps($mockBotService);

        $mockCommandCore = $this->getMockBuilder(PrivateChatCommandCore::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Asserting that returned value is an array count - 2
        $mockBotService->expects($this->once())
            ->method('setPrivateChatCommand')
            ->with("third text");

        $mockCommandCore->expects($this->once())
            ->method('handle');

        (new Menu($mockBotService, $mockCommandCore))->back();
    }

    // TODO : add tests for refresh function

    /**
     * Assert that menu pointer indicates the last element in cache 
     * which means that returned value is the last element of an array 
     */
    private function mockBotServiceGetAdminMethod($mockBotService)
    {
        $mockBotService->expects($this->any())
            ->method('getAdmin')
            ->willReturn($this->admin);
    }

    public function assertBackMenuArrayHas(array $array)
    {
        $backMenuArray = $this->getBackMenuArray($this->admin->admin_id);
        foreach ($array as $value) {
            $this->assertTrue(in_array($value, $backMenuArray));
        }
    }

    public function saveValuesToBackMenuArray(array $array)
    {
        foreach ($array as $value) {
            $mockService = $this->createMock(TelegramBotService::class);
            $this->mockBotServiceGetPrivateChatCommandMethod($value, $mockService);
            $this->mockBotServiceGetAdminMethod($mockService);
            $menu = new Menu($mockService, $this->createMock(PrivateChatCommandCore::class));
            $menu->save();
        }
    }

    private function prepareMenuConstructDeps($mockBotService)
    {
        $this->mockBotServiceGetPrivateChatCommandMethod("some command", $mockBotService);
        $this->mockBotServiceGetAdminMethod($mockBotService);
    }

}
