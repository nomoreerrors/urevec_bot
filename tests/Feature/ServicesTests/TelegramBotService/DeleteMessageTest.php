<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Services\CONSTANTS;
use App\Models\MessageModel;
use App\Models\TextMessageModel;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;

class DeleteMessageTest extends TestCase
{
    private $data;
    private $model;
    private $botService;

    public function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
        $this->model = (new BaseTelegramRequestModel($this->data))->getModel();
        $this->botService = new TelegramBotService($this->model);
    }

    public function testDeleteMessageStatusOkReturnsNull(): void
    {
        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->getMessageModelData()
            ], 200)
        ]);

        $this->assertNull($this->botService->deleteMessage());
    }

    /**
     * Test that a message deletion request with a non-existent message ID returns false.
     * @return void
     */
    public function testDeleteUnexistentMessageThrowsException(): void
    {
        Http::fake([
            '*' => Http::response([
                "ok" => false,
                "error_code" => 400,
                "description" => "Bad Request: message to delete not found",
                'result' => $this->getMessageModelData()
            ], 404)
        ]);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::DELETE_MESSAGE_FAILED);
        $this->botService->deleteMessage();
    }

    public function testNotAMessageModelInstanceThrowsException(): void
    {
        $this->model = (new BaseTelegramRequestModel($this->getNewMemberJoinUpdateModelData()))->getModel();
        $this->botService = new TelegramBotService($this->model);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::DELETE_MESSAGE_FAILED .
            CONSTANTS::WRONG_INSTANCE_TYPE);
        $this->botService->deleteMessage();
    }
}
