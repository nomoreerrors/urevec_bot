<?php

namespace Feature\PrivateChatCommandRegister;
use Tests\TestCase;
use App\Classes\PrivateChatCommandRegister;
use App\Models\Chat;
use App\Services\TelegramBotService;

class GetMyCommandsTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSendRequestWithExpectedParams(): void
    {
        $this->botService = $this->createMock(TelegramBotService::class);
        $this->botService->method('getChat')->willReturn($this->createMock(Chat::class));

        $expectedScope = [
            "scope" => [
                "type" => 'chat',
                "chat_id" => 123
            ]
        ];

        $fakeResponse = $this->createMock(\Illuminate\Http\Client\Response::class);
        $fakeResponse->method('json')->willReturn(
            [
                'result' => [
                    'commands' => []
                ]
            ]

        );

        // General test
        $this->botService->method('sendPost')->
            with('getMyCommands', $expectedScope)
            ->willReturn($fakeResponse);

        $privateChatCommandRegister = $this->getMockBuilder(PrivateChatCommandRegister::class)
            ->setConstructorArgs([$this->botService])
            ->onlyMethods([])
            ->getMock();


        $result = $privateChatCommandRegister->getMyCommands('chat', 123);
        //Assert that the "result" key not exists in the response because
        // the return value of the method should be "return $result['result']"
        $this->assertEquals($result, ['commands' => []]);
    }

}
