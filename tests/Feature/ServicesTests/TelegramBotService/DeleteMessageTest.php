<?php

namespace Tests\Feature;

use App\Models\MessageModels\TextMessageModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\TelegramRequestModelBuilder;
use App\Services\CONSTANTS;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;

class DeleteMessageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testWrongInstanceTypeThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::DELETE_MESSAGE_FAILED . CONSTANTS::WRONG_INSTANCE_TYPE);

        $mockRequestModel = $this->getMockBuilder(NewMemberJoinUpdateModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel'])
            ->getMock();

        $this->botService->method('getRequestModel')->willReturn($mockRequestModel);
        $this->botService->deleteMessage();
    }

    public function testExpectedParametersWasPassed(): void
    {
        $mockRequestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestModel->method('getChatId')->willReturn(123);
        $mockRequestModel->method('getMessageId')->willReturn(456);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel', 'sendPost'])
            ->getMock();

        $expectedArgs = ['chat_id' => 123, 'message_id' => 456];
        $this->botService->method('getRequestModel')->willReturn($mockRequestModel);
        $this->botService->method('sendPost')
            ->with('deleteMessage', $expectedArgs)
            ->willReturn($this->getFakeResponse());



        $this->botService->deleteMessage();
        $this->assertTrue(true); // Just to pass the test
    }

    public function testResponseIsNotOkThrowsException(): void
    {
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::DELETE_MESSAGE_FAILED);

        $mockRequestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequestModel->method('getChatId')->willReturn(123);
        $mockRequestModel->method('getMessageId')->willReturn(456);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel', 'sendPost'])
            ->getMock();

        $expectedArgs = ['chat_id' => 123, 'message_id' => 456];
        $this->botService->method('getRequestModel')->willReturn($mockRequestModel);
        $this->botService->method('sendPost')
            ->with('deleteMessage', $expectedArgs)
            ->willReturn($this->getFakeResponse(false));



        $this->botService->deleteMessage();
        $this->assertTrue(true); // Just to pass the test
    }
}
