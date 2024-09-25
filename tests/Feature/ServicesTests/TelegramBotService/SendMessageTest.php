<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\TextMessageModel;
use Illuminate\Http\Client\Response;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    private string $textMessage = "Hello, world!";

    private array $params = [
        "chat_id" => 12345,
        "text" => "Hello, world!",
    ];

    private array $replyMarkup =
        [
            "inline_keyboard" => [
                [
                    ["text" => "Button 1"],
                    ["text" => "Button 2",]
                ]
            ]
        ];


    protected function setUp(): void
    {
        parent::setUp();
        $this->params['reply_markup'] = $this->replyMarkup;
    }

    /**
     * Test that the sendMessage method sends a message successfully.
     *
     * @test
     * @return void
     */
    public function testSendMessageSendsMessageSuccessfully(): void
    {
        $requestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getChatId"])
            ->getMock();

        $requestModel->method("getChatId")->willReturn(12345);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["sendPost", "getRequestModel"])
            ->getMock();

        $this->botService->method("getRequestModel")->willReturn($requestModel);


        $this->botService->expects($this->once())
            ->method("sendPost")
            ->with(
                "sendMessage",
                $this->params
            )
            ->willReturn($this->getFakeResponse());

        $this->botService->sendMessage($this->textMessage, $this->replyMarkup);
    }


    /**
     * Test that the sendMessage method throws an exception when the response is not OK.
     *
     * @test
     * @return void
     */
    public function testSendMessageThrowsExceptionWhenResponseIsNotOk(): void
    {
        $requestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getChatId"])
            ->getMock();

        $requestModel->method("getChatId")->willReturn(12345);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["sendPost", "getRequestModel"])
            ->getMock();

        $this->botService->method("getRequestModel")->willReturn($requestModel);


        $this->botService->expects($this->once())
            ->method("sendPost")
            ->with(
                "sendMessage",
                $this->params
            )
            ->willReturn($this->getFakeResponse(false));

        // Act & Assert
        $this->expectException(BaseTelegramBotException::class);
        $this->expectExceptionMessage(CONSTANTS::SEND_MESSAGE_FAILED);
        $this->botService->sendMessage($this->textMessage, $this->replyMarkup);
    }


    public function testParametersWIthoutReplyMarkupSendCorrectly(): void
    {
        $requestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getChatId"])
            ->getMock();

        $requestModel->method("getChatId")->willReturn(12345);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["sendPost", "getRequestModel"])
            ->getMock();

        $this->botService->method("getRequestModel")->willReturn($requestModel);


        unset($this->params['reply_markup']);

        $this->botService->expects($this->once())
            ->method("sendPost")
            ->with(
                "sendMessage",
                $this->params
            )
            ->willReturn($this->getFakeResponse(true));

        $this->botService->sendMessage($this->textMessage);
    }
}