<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Models\MessageModel;
use App\Models\TextMessageModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Cache;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;

class SetMyCommandsTest extends TestCase
{
    use RefreshDatabase;
    protected $data;
    protected $chatId;
    protected $adminsIdsCacheKey;
    protected $adminsIdsArray;
    protected $model;
    protected $botService;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = $this->getMultiMediaModelData();
        $this->chatId = $this->data["message"]["chat"]["id"];
        $this->adminsIdsCacheKey = CONSTANTS::CACHE_CHAT_ADMINS_IDS . $this->chatId;
        $this->adminsIdsArray = [123, 456, 789];
        //Put cache here so that it can be used in BaseTelegramRequestModel and setMyCommands method wont post to api
        Cache::put($this->adminsIdsCacheKey, $this->adminsIdsArray);

        $this->model = new BaseTelegramRequestModel($this->data);
        $this->botService = new TelegramBotService($this->model);
    }

    public function testMyCommandsAdminsArrayEmptyThrowsException(): void
    {
        Cache::delete($this->adminsIdsCacheKey);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SET_MY_COMMANDS_FAILED .
            CONSTANTS::CACHE_ADMINS_IDS_NOT_SET);
        $this->assertFalse(Cache::get("MyCommandsSet") == "true");
        $this->botService->setMyCommands();
    }

    /**
     * Set group chat commands visibility for admins and cache the result in cache table
     * if admins array is not empty
     */
    public function testAdminsArrayNotEmptyGroupChatVisibilityIsSet(): void
    {
        Cache::put($this->adminsIdsCacheKey, $this->adminsIdsArray);

        $this->botService->setMyCommands();
        $this->assertTrue(Cache::has($this->adminsIdsCacheKey));
        $this->assertTrue(Cache::has(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->chatId));
    }

    /**
     * Set private chat commands visibility for admins and cache the result in cache table
     */
    public function testSetPrivateChatCommandsVisibilityForAdmins(): void
    {
        $privateChatVisibilityCacheKey = CONSTANTS::CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY . $this->chatId;

        Cache::put($this->adminsIdsCacheKey, $this->adminsIdsArray);
        $this->botService->setMyCommands();

        $this->assertTrue(Cache::has($this->adminsIdsCacheKey));
        $this->assertTrue(Cache::has($privateChatVisibilityCacheKey));

        $cachedAdminsIds = Cache::get($privateChatVisibilityCacheKey);
        //Asserting that the admins ids are cached
        $this->assertNotNull($cachedAdminsIds);
        //Asserting that all admins ids are in the array
        $this->assertEmpty(array_diff($cachedAdminsIds, $this->adminsIdsArray));
    }


    public function testCommandsVisibilityNotSetWhenAdminsArrayNotEmptyAndVisibilityAlreadyCached(): void
    {
        // Set up the cache with the visibility already cached
        Cache::put(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->chatId, "enabled");

        $this->botService->setMyCommands();

        // Assert that the visibility was not set again
        $this->assertEquals("enabled", Cache::get(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->chatId));
    }

    /**
     * Request to real Telegram Api to make sure everything is fine
     * @return void
     */
    public function testCommandsVisibilitySetForGroupChatWhenAdminsArrayNotEmptyAndVisibilityNotCached(): void
    {
        // Clear the cache
        Cache::forget(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->chatId);

        Http::fake([
            '*' => Http::response(
                [
                    "ok" => true,
                    "result" => [
                        "0" => [
                            "user" => [
                                "id" => $this->adminsIdArray[0]
                            ],

                            "1" => [
                                "user" => [
                                    "id" => $this->adminsIdArray[1]
                                ]
                            ]
                        ]
                    ]
                ],
                200
            ), // Replace '*' with the actual URL you are making the request to
        ]);
        $this->botService->setMyCommands();

        // Assert that the visibility was set
        $this->assertTrue(Cache::has(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->chatId));
    }

    public function testCommandsVisibilitySetForPrivateChatWhenAdminsArrayNotEmptyAndVisibilityNotCached(): void
    {
        // Clear the cache
        Cache::forget(CONSTANTS::CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY . $this->chatId);

        Http::fake([
            '*' => Http::response(
                [
                    "ok" => true,
                    "result" => [
                        "0" => [
                            "user" => [
                                "id" => $this->adminsIdArray[0]
                            ],

                            "1" => [
                                "user" => [
                                    "id" => $this->adminsIdArray[1]
                                ]
                            ]
                        ]
                    ]
                ],
                200
            ), // Replace '*' with the actual URL you are making the request to
        ]);

        $this->botService->setMyCommands();

        // Assert that the visibility was set
        $this->assertTrue(Cache::has(CONSTANTS::CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY . $this->chatId));
    }
}





