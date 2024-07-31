<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    private $data;
    private $model;
    protected $service;
    private $testMessage = "His name is Robert Paulsen";

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->service = new TelegramBotService($this->model);
    }

    public function testSendMessageReturnStatusTrue(): void
    {
        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->getMultiMediaModelData()
            ], 200)
        ]);

        $this->assertNull($this->service->sendMessage($this->testMessage));
    }

    public function testSendMessageThrowsExceptionIfStatusFalse(): void
    {
        Http::fake([
            '*' => Http::response([
                'ok' => false,
                'description' => 'User not found',
                'result' => $this->getMultiMediaModelData()
            ], 404)
        ]);

        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SEND_MESSAGE_FAILED);
        $this->expectExceptionMessage(CONSTANTS::SEND_MESSAGE_FAILED);
        $this->service->sendMessage($this->testMessage);
    }
}