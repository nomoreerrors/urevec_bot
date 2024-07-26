<?php

namespace Tests\Feature;

use App\Exceptions\UnknownChatException;
use App\Models\Eloquent\BotChat;
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
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramBotService;

class BaseTelegramRequestModelTest extends TestCase
{
    use RefreshDatabase;
    private array $data;

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
    public function testSetAdminsIdsFunctionGetsIdsFromDatabase(): void
    {
        // TelegramBotService needs the commandsList to create a complete chat in database
        app()->singleton("commandsList", fn() => new CommandsList());
        // Fake response so that BaseTelegramRequestModel assign fake Ids to property adminsIds
        $this->fakeResponseWithAdminsIds(1000, 2000);
        // Assigning fake Ids to property adminsIds
        $requestModel = new BaseTelegramRequestModel($this->data);
        //Creating a new chat in database with fake admins ids
        (new TelegramBotService($requestModel))->createChat();
        // Fake response with two another values 
        $this->fakeResponseWithAdminsIds(5000, 7000);
        // BaseTelegramRequestModel assigns fake ids once again
        $requestModel = new BaseTelegramRequestModel($this->data);
        // Asserting that the ids didn't change because they are from database this time and Http call didn't happen
        $this->assertContains(1000, $requestModel->getAdminsIds());
        $this->assertContains(2000, $requestModel->getAdminsIds());
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
        $model = new BaseTelegramRequestModel($this->data);
        $this->assertContains(456, $model->getAdminsIds());
        $this->assertContains(789, $model->getAdminsIds());
    }

    public function testSetAdminsIdsTrowsExceptionIfHttpCallFails(): void
    {
        $this->fakeResponseWithAdminsIds(1000, 2000, false);
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::GET_ADMINS_FAILED);

        (new BaseTelegramRequestModel($this->data));
    }


    public function fakeResponseWithAdminsIds(int $id, int $secondId, bool $status = true)
    {
        return Http::fake(fn() => Http::response([
            'ok' => $status,
            'description' => 'ok',
            'result' => [
                ['user' => ['id' => $id]], // Admin 1
                ['user' => ['id' => $secondId]] // Admin 2
            ]
        ], 200));
    }

}