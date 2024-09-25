<?php

namespace Feature\ServicesTests\TelegramBotService;
use App\Models\MessageModels\TextMessageModel;
use App\Enums\ResTime;
use App\Services\TelegramBotService;
use Tests\TestCase;
use Tests\Feature\Traits\MockBotService;

class BanUserTest extends TestCase
{
    use MockBotService;

    public function setUp(): void
    {
        $this->mockBotService = $this->getMockBuilder(TelegramBotService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'shouldDeleteUser',
                'deleteUser',
                'sendMessage',
                'getRequestModel',
                'restrictChatMember',
                'getChatRestrictionTime'
            ])
            ->getMock();

        //Expect message send and it contains user name
        $this->mockBotService->expects($this->once())
            ->method('sendMessage')
            ->with($this->stringContains('John Doe'));

        $requestModel = $this->createMock(TextMessageModel::class);
        $requestModel->expects($this->once())
            ->method('getFromUserName')
            ->willReturn('John Doe');

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($requestModel);
    }

    public function testIfShouldDeleteUserIsTrueDeleteUserAndReturn(): void
    {
        $this->mockBotService->expects($this->once())
            ->method('shouldDeleteUser')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('deleteUser');

        $this->mockBotService->expects($this->never())
            ->method('restrictChatMember');

        $this->mockBotService->banUser();
    }

    public function testIfShouldDeleteUserIsFalseRestrictChatMemberWithDefaultTime(): void
    {
        $this->mockBotService->expects($this->once())
            ->method('shouldDeleteUser')
            ->willReturn(false);

        $this->mockBotService->expects($this->never())
            ->method('deleteUser');


        $this->mockBotService->expects($this->once())
            ->method('getChatRestrictionTime')
            ->willReturn(ResTime::WEEK);


        $this->mockBotService->expects($this->once())
            ->method('restrictChatMember')
            ->with(ResTime::WEEK);


        $this->mockBotService->banUser();
    }

    public function testRestrictChatMemberWithtTimePassedAsArgument(): void
    {
        $this->mockBotService->expects($this->once())
            ->method('shouldDeleteUser')
            ->willReturn(false);

        $this->mockBotService->expects($this->never())
            ->method('deleteUser');

        // Make sure that it never gets called 
        $this->mockBotService->expects($this->never())
            ->method('getChatRestrictionTime');


        //Make sure that the time is getting from the argument instead
        $this->mockBotService->expects($this->once())
            ->method('restrictChatMember')
            ->with(ResTime::MONTH);


        $this->mockBotService->banUser(ResTime::MONTH);

    }
}
