<?php

namespace Tests\Feature;

use App\Models\MessageModels\TextMessageModel;
use App\Enums\RestrictChatMemberData;
use App\Exceptions\BaseTelegramBotException;
use App\Models\Chat;
use Illuminate\Mail\TextMessage;
use TypeError;
use App\Enums\ResTime;
use App\Exceptions\RestrictChatMemberFailedException;
use App\Services\CONSTANTS;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\DB;

class RestrictChatMemberTest extends TestCase
{
    public function testEmptyBanReasonModelNameThrowsException()
    {
        $this->expectException(RestrictChatMemberFailedException::class);
        $this->expectExceptionMessage(CONSTANTS::RESTRICT_MEMBER_FAILED);
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getBanReasonModelName'])
            ->getMock();

        $this->botService->method('getBanReasonModelName')->willReturn("");

        $this->botService->restrictChatMember(ResTime::DAY);
    }

    public function testWrongResTimeArgumentTypeThrowsException()
    {
        $this->expectException(\TypeError::class);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods([])
            ->getMock();

        $this->botService->restrictChatMember(242411);
    }

    public function testWrongIdArgumentTypeThrowsException()
    {
        $this->expectException(\TypeError::class);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods([])
            ->getMock();

        $this->botService->restrictChatMember(ResTime::DAY, "string");
    }

    public function testParametersCanBeNull()
    {
        $this->throwExceptionToStopTest();
        //Run with empty parameters
        $this->botService->restrictChatMember();
    }

    public function testIfEmptyModelThrowsException()
    {
        $this->expectException(RestrictChatMemberFailedException::class);
        $this->expectExceptionMessage(CONSTANTS::UNKNOWN_MODEL);
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getBanReasonModelName', 'getChat'])
            ->getMock();

        $this->botService->method('getChat')->willReturn($this->createMock(Chat::class));
        $this->botService->method('getBanReasonModelName')->willReturn("unknown_model");

        $this->botService->restrictChatMember(ResTime::DAY);
    }

    public function testIfNotEmptyModelExceptionNotThrown()
    {
        $admin = $this->setAdminWithMultipleChats(1);
        $chat = $admin->chats()->first();

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getBanReasonModelName', 'getChat', 'getRequestModel', 'sendPost'])
            ->getMock();

        $requestModel = $this->mockRequestModel($chat->chat_id);

        $this->botService->method('getRequestModel')->willReturn($requestModel);
        $this->botService->method('getChat')->willReturn($chat);
        // Return an existing model
        $this->botService->method('getBanReasonModelName')->willReturn("badWordsFilter");
        $this->botService->expects($this->once())->method('sendPost')->willReturn($this->getFakeResponse(true));

        $this->botService->restrictChatMember(ResTime::DAY);
    }

    /**
     * @method getRestrictionData
     * @return void
     */
    public function testReturnCorrectRestrictionData()
    {
        $admin = $this->setAdminWithMultipleChats(1);
        $chat = $admin->chats()->first();


        $expectedData = [
            RestrictChatMemberData::CHAT_ID->value => $chat->chat_id,
            RestrictChatMemberData::USER_ID->value => 123,
            RestrictChatMemberData::CAN_SEND_MESSAGES->value => 1,
            RestrictChatMemberData::CAN_SEND_DOCUMENTS->value => 0,
            RestrictChatMemberData::CAN_SEND_PHOTOS->value => 0,
            RestrictChatMemberData::CAN_SEND_VIDEOS->value => 0,
            RestrictChatMemberData::CAN_SEND_VIDEO_NOTES->value => 0,
            RestrictChatMemberData::CAN_SEND_OTHER_MESSAGES->value => 0,
            RestrictChatMemberData::UNTIL_DATE->value => 12345
        ];


        //mock methods except for getRestrictionData
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods([
                'getBanReasonModelName',
                'getChat',
                'getUntilDate',
                'getRequestModel',
                'sendPost',
                'getChatRestrictionTime',
                'getRestrictStatusFromModel',
            ])
            ->getMock();

        $requestModel = $this->mockRequestModel($chat->chat_id, 123);


        $this->botService->method('getChatRestrictionTime')->willReturn(ResTime::DAY);
        $this->botService->method('getRequestModel')->willReturn($requestModel);
        $this->botService->method('getChat')->willReturn($chat);
        //Prepare parameters
        $this->botService->method('getUntilDate')->willReturn(12345);
        $this->botService->method('getRestrictStatusFromModel')->willReturn([
            'canSendMessages' => 1, //To make sure that only can_send_messages is getting value from $status['canSendMessages']
            'canSendMedia' => 0
        ]);

        // Return an existing model
        $this->botService->method('getBanReasonModelName')->willReturn("badWordsFilter");
        $this->botService->expects($this->once())->method('sendPost')
            ->with('restrictChatMember', $expectedData)
            ->willReturn($this->getFakeResponse(true));

        $this->botService->restrictChatMember();
    }

    public function testResponseIsNotOkThenThrowException()
    {
        $this->expectException(RestrictChatMemberFailedException::class);
        $this->expectExceptionMessage(CONSTANTS::RESTRICT_MEMBER_FAILED);

        //Skip methods:
        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods([
                'sendPost',
                'getUntilDate',
                'getRestrictStatusFromModel',
                'getRestrictionData'
            ])
            ->getMock();

        //Fake false response
        $this->botService->expects($this->once())->
            method('sendPost')->willReturn($this->getFakeResponse(false));

        $this->botService->restrictChatMember();
    }

    public function testSendPostWithCorrectValues()
    {
        $admin = $this->setAdminWithMultipleChats(1);
        $chat = $admin->chats()->first();


        $restrictionData = [
            RestrictChatMemberData::CHAT_ID->value => $chat->chat_id,
            RestrictChatMemberData::USER_ID->value => 123,
            RestrictChatMemberData::CAN_SEND_MESSAGES->value => 0,
            RestrictChatMemberData::CAN_SEND_DOCUMENTS->value => 0,
            RestrictChatMemberData::CAN_SEND_PHOTOS->value => 0,
            RestrictChatMemberData::CAN_SEND_VIDEOS->value => 0,
            RestrictChatMemberData::CAN_SEND_VIDEO_NOTES->value => 0,
            RestrictChatMemberData::CAN_SEND_OTHER_MESSAGES->value => 0,
            RestrictChatMemberData::UNTIL_DATE->value => 12345
        ];

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods([
                'getBanReasonModelName',
                'getChat',
                'getRequestModel',
                'sendPost',
                'getChatRestrictionTime',
                'getRestrictionData'
            ])
            ->getMock();

        $requestModel = $this->mockRequestModel($chat->chat_id, 123);

        $this->botService->method('getRestrictionData')->willReturn($restrictionData);
        $this->botService->method('getChatRestrictionTime')->willReturn(ResTime::DAY);
        $this->botService->method('getRequestModel')->willReturn($requestModel);
        $this->botService->method('getChat')->willReturn($chat);
        // Return an existing model
        $this->botService->method('getBanReasonModelName')->willReturn("badWordsFilter");
        $this->botService->expects($this->once())->method('sendPost')
            ->with(
                'restrictChatMember',
                $restrictionData
            )
            ->willReturn($this->getFakeResponse(true));

        $this->botService->restrictChatMember();
    }


    private function throwExceptionToStopTest()
    {
        //Throw an exception just to stop test executing
        $this->expectException(RestrictChatMemberFailedException::class);

        $this->botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getBanReasonModelName'])
            ->getMock();

        $this->botService->method('getBanReasonModelName')->willReturn("");
    }


    private function mockRequestModel(int $chat_id = 123, int $from_id = 123)
    {
        $requestModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChatId', 'getFromId'])
            ->getMock();

        $requestModel->method('getChatId')->willReturn($chat_id);
        $requestModel->method('getFromId')->willReturn($from_id);
        return $requestModel;
    }
}
