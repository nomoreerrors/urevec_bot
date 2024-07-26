<?php

namespace Tests\Feature;

use App\Exceptions\BaseTelegramBotException;
use App\Services\CONSTANTS;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\DB;

class RestrictChatMemberTest extends TestCase
{
    use RefreshDatabase;

    protected $requestData;

    protected int $updateId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestData = $this->getMessageModelData();
        $this->updateId = $this->requestData["update_id"];

    }
    /**
     * Test that restricting a user by ID returns true.
     * @return void
     */
    public function testRestrictUserByIdReturnsTrue()
    {
        $requestModel = new BaseTelegramRequestModel($this->requestData);
        $requestModel->getModel();
        $service = new TelegramBotService($requestModel);

        $this->assertTrue($service->restrictChatMember());
    }


    /**
     * Request to real Telegram API should be dispatched to queue when restricting user fails.
     * and exception should be thrown.
     * @return void
     */
    public function testExceptionIsThrownAndRequestDispatchedToQueueWhenRestrictingUserFails(): void
    {
        $this->requestData['message']['from']['id'] = $this->getInvalidUserId();

        $requestModel = new BaseTelegramRequestModel($this->requestData);
        $requestModel->getModel();
        $telegramBotService = new TelegramBotService($requestModel);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::RESTRICT_MEMBER_FAILED);

        $telegramBotService->restrictChatMember();

        $jobInQueue = DB::table('jobs')
            ->where('payload', 'like', "%{$this->updateId}%")
            ->first();

        $this->assertNotNull($jobInQueue);
    }
}
