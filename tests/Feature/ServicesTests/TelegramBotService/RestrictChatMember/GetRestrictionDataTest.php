<?php

namespace Feature\ServicesTests\TelegramBotService\RestrictChatMember;

use Tests\TestCase;
use App\Enums\RestrictChatMemberData;
use App\Models\MessageModels\TextMessageModel;
use App\Services\TelegramBotService;

class GetRestrictionDataTest extends TestCase
{

    /**
     * Test that the user_id value is taken from the id argument and not from getFromId method 
     * $id ?? $this->getRequestModel()->getFromId() which means that this expression not deleted
     * @method getRestrictionData
     * @return void
     */
    public function testGetRestrictionDataAssignUserIdFromIdArgument()
    {
        $args['status'] = ['canSendMessages' => 1, 'canSendMedia' => 0];
        $args['until_date'] = 12345;
        $args['id'] = 555;

        //Set fromId to 00000 to ensure it won't be used 
        $requestModel = $this->mockRequestModel(12345, 0000);

        $botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel'])
            ->getMock();

        $botService->method('getRequestModel')->willReturn($requestModel);
        $result = $this->getAccessToProtectedMethodAndInvoke('getRestrictionData', $botService, $args);
        // Assert that  user_id is 555 and not 00000
        $this->assertEquals($result['user_id'], $args['id']);
    }


    /**
     * Test that the user_id value is taken from getFromId method if Id argument not given
     * $id ?? $this->getRequestModel()->getFromId() which means that this expression not deleted
     * @method getRestrictionData
     * @return void
     */
    public function testGetRestrictionDataAssignUserIdFromGetFromIdMethod()
    {
        //Set args without id
        $args['status'] = ['canSendMessages' => 1, 'canSendMedia' => 0];
        $args['until_date'] = 12345;

        $expectedId = 11111;

        //Set fromId to 11111 to ensure it will be used
        $requestModel = $this->mockRequestModel(12345, $expectedId);

        $botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel'])
            ->getMock();

        $botService->method('getRequestModel')->willReturn($requestModel);
        $result = $this->getAccessToProtectedMethodAndInvoke('getRestrictionData', $botService, $args);
        // Assert that  user_id is 11111 
        $this->assertEquals($result['user_id'], $expectedId);
    }

    /*
     * @method getRestrictionData
     * @return void
     */
    public function testAllEnumValuesAreInArray()
    {
        //random args
        $args['status'] = ['canSendMessages' => 1, 'canSendMedia' => 0];
        $args['until_date'] = 12345;

        //Model with random values
        $requestModel = $this->mockRequestModel(12345, 6789);

        $botService = $this->getMockBuilder(TelegramBotService::class)
            ->onlyMethods(['getRequestModel'])
            ->getMock();

        $botService->method('getRequestModel')->willReturn($requestModel);
        $result = $this->getAccessToProtectedMethodAndInvoke('getRestrictionData', $botService, $args);

        foreach (RestrictChatMemberData::cases() as $enum) {
            $this->assertArrayHasKey($enum->value, $result);
        }
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
