<?php

namespace Feature\ServicesTests\TelegramBotService;

use App\Models\TelegramRequestModelBuilder;
use App\Services\CONSTANTS;
use App\Exceptions\DeleteUserFailedException;
use Illuminate\Support\Facades\Http;
use Response;
use Tests\TestCase;
use App\Services\TelegramBotService;
use App\Models\MessageModels\TextMessageModel;
use Tests\Feature\Traits\MockBotService;

class DeleteUserTest extends TestCase
{
    use MockBotService;

    private $responseMock;

    private $mockRequestModel;


    /**
     * Test that delete user in unexistent chat returns 'not found' 404 error
     * which means that the api.telegram.bla-bla/banChatMember request is valid, only chat is not found
     * @return void
     */
    public function testDeleteUserApiCallWithFakeChatIdShouldReturnNotFoundError(): void
    {
        $requestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChatId', 'getFromId'])
            ->getMock();

        $botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['sendPost', 'getRequestModel'])
            ->getMock();
        $botService->method('getRequestModel')->willReturn($requestModel);

        $this->expectException(DeleteUserFailedException::class);

        $response = $botService->deleteUser();
        $this->assertEquals('Not Found', $response['description']);
        $this->assertEquals(404, $response['error_code']);
    }


    public function testExpectedArgumentsPassed(): void
    {
        $this->mockBotServiceWitRequestModel();
        $this->responseMock->expects($this->once())
            ->method('Ok')
            ->willReturn(true);

        $this->responseMock->expects($this->once())
            ->method('json')
            ->willReturn([]);

        $this->mockBotService->expects($this->once())
            ->method('sendPost')
            ->with(
                'banChatMember',
                [
                    'chat_id' => 123,
                    'user_id' => 456
                ]
            )->willReturn($this->responseMock);

        $this->mockBotService->deleteUser();
    }

    public function testThrowExceptionIfResponseIsNotOk(): void
    {
        $this->mockBotServiceWitRequestModel();
        $this->mockBotService->expects($this->any())
            ->method('sendPost')
            ->willReturn($this->responseMock);

        $this->expectException(DeleteUserFailedException::class);
        $this->expectExceptionMessage(CONSTANTS::DELETE_USER_FAILED);
        $this->mockBotService->deleteUser();
    }

    private function mockBotServiceWitRequestModel()
    {
        $this->responseMock = $this->getMockBuilder(\Illuminate\Http\Client\Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockRequestModel = $this->createMock(TextMessageModel::class);
        $this->mockRequestModel->expects($this->once())
            ->method('getChatId')
            ->willReturn(123);

        $this->mockRequestModel->expects($this->once())
            ->method('getFromId')
            ->willReturn(456);

        $this->mockBotService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['sendPost', 'getRequestModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockBotService->expects($this->any())
            ->method('getRequestModel')
            ->willReturn($this->mockRequestModel);
    }
}
