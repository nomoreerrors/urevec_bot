<?php

namespace Tests\Feature;

use App\Exceptions\UnknownChatException;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
use Illuminate\Support\Facades\Http;
use Mockery;
use App\Services\CONSTANTS;
use App\Classes\CommandsList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Http\Client\Response;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramBotService;

class TelegramRequestModelBuilderTest extends TestCase
{
    use RefreshDatabase;

    private int $chatId;

    private string $cacheKey;

    public function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
        $this->chatId = $this->data["message"]["chat"]["id"];
    }

    /**
     * Testcase where setAdminsIds function gets ids from database if it exists 
     * @return void
     */
    public function testSetAdminsFunctionGetsIdsFromDatabase(): void
    {
        (new SimpleSeeder)->run(1, 5);
        $chat = Chat::first();
        $adminId = $chat->admins->first()->admin_id;
        $this->data["message"]["chat"]["id"] = $chat->chat_id;
        // // Assigning fake Ids to property adminsIds
        $requestModel = new TelegramRequestModelBuilder($this->data);
        // Asserting that the ids didn't change because they are from database this time and Http call didn't happen
        $this->assertContains($adminId, $requestModel->getAdminsIds());
    }

    /**
     * SetAdminsIds function should call Http::fake if it doesn't get ids from database
     * @return void
     */
    public function testSetAdminsIdsByMakingHttpRequest(): void
    {
        // Database is cleared before each test by RefreshDatabase trait
        // Mock the HTTP client to return a fake response
        $this->fakeResponseWithAdminsIds(456, 789);
        // Assigning fake Ids to property adminsIds
        $model = new TelegramRequestModelBuilder($this->data);
        $this->assertContains(456, $model->getAdminsIds());
        $this->assertContains(789, $model->getAdminsIds());
    }

    public function testSetAdminsIdsTrowsExceptionIfHttpCallFails(): void
    {
        $this->fakeResponseWithAdminsIds(1000, 2000, false);
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::GET_ADMINS_FAILED);

        (new TelegramRequestModelBuilder($this->data));
    }

}