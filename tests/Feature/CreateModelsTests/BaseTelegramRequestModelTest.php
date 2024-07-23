<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Mockery;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Http\Client\Response;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramBotService;

class BaseTelegramRequestModelTest extends TestCase
{
    private array $data;

    private int $chatId = 123456;

    private string $cacheKey;

    public function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
        $this->data["message"]["chat"]["id"] = $this->chatId;
        $this->cacheKey = CONSTANTS::CACHE_CHAT_ADMINS_IDS . $this->chatId;

    }

    /**
     * Test the setAdminsIds method of the BaseTelegramRequestModel class.
     *
     * This method tests the functionality of the setAdminsIds method, which retrieves
     * a list of admin IDs from Telegram Bot API for a given chat ID and stores them in the cache.
     *
     * @return void
     */
    public function testSetAdminsIdsFunctionPutsAdminsIdsInCache()
    {
        Cache::delete($this->cacheKey); // Clear the cache before running the test

        // Mock the HTTP client to return a fake response
        Http::fake(fn() => Http::response([
            'ok' => true,
            'result' => [
                ['user' => ['id' => 456]], // Admin 1
                ['user' => ['id' => 789]] // Admin 2
            ]
        ], 200)); // The response status code

        // Create a new instance of the BaseTelegramRequestModel class and the setAdminsIds method will  be calling automatically
        (new BaseTelegramRequestModel($this->data));
        // Assert that the cache key with the list of admins ids exists in the cache
        $this->assertTrue(Cache::has($this->cacheKey));
    }

    /**
     * Asserting that the setAdminsIds function gets admins ids from cache if it exists and Http::post method is not called
     * @return void
     */
    public function testSetAdminsIdsFunctionGetsAdminsIdsFromCacheIfItExists(): void
    {
        Http::fake(fn() => Http::response([
            'ok' => true,
            'result' => [
                ['user' => ['id' => 1000]], // Admin 1
                ['user' => ['id' => 2000]] // Admin 2
            ]
        ], 200));

        (new BaseTelegramRequestModel($this->data));
        $adminIds = Cache::get($this->cacheKey);

        $this->assertFalse(in_array(1000, $adminIds));
        $this->assertFalse(in_array(2000, $adminIds));
        // Value should be in the cache from the previous test
        $this->assertTrue(in_array(456, $adminIds));
    }
}