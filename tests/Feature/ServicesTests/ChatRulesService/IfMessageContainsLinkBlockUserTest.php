<?php

namespace Tests\Feature;

use App\Enums\BanMessages;
use App\Models\LinksFilter;
use App\Models\MessageModels\TextMessageModel;
use App\Models\StatusUpdateModel;
use App\Enums\ResTime;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Services\ChatRulesService;
use App\Services\TelegramBotService;
use App\Models\Admin;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class IfMessageContainsLinkBlockUserTest extends TestCase
{
    use RefreshDatabase;
    use MockBotService;

    private ChatRulesService $ruleService;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->fakeSendMessageSucceedResponse();
        // $this->fakeDeleteMessageSucceedResponse();
        // $this->fakeRestrictMemberSucceedResponse();

        // (new SimpleSeeder())->run(1, 5);
        $this->setAdminWithMultipleChats(2);
        $this->chat = Chat::first();
        // $this->admin = $this->chat->admins->first();
        // $this->data = $this->getMessageModelData();
        // $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        // $this->clearTestLogFile();
    }

    /**
     * Tescase where is ifMessageHasLinkBlockUser() returns false if user is administrator
     * @method ifMessageHasLinkBlockUser
     * @return void
     */
    public function test_message_has_link_but_user_is_administrator_should_returns_false()
    {
        $this->mockBotCreate();
        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFromAdmin', 'getHasTextLink', 'getHasLink'])
            ->getMock();

        $mockModel->method('getFromAdmin')
            ->willReturn(true);

        $mockModel->method('getHasTextLink')
            ->willReturn(true);

        $mockModel->method('getHasLink')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);

        $this->mockBotService->expects($this->never())
            ->method('banUser');

        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime'])
            ->getMock();

        $chatRulesService->expects($this->never())
            ->method('shouldRestrictUser');

        $chatRulesService->expects($this->never())
            ->method('getRestrictionTime');

        $this->assertFalse($chatRulesService->ifMessageHasLinkBlockUser());
    }

    public function test_message_has_text_link_should_returns_true()
    {
        $this->mockBotCreate();
        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFromAdmin', 'getHasTextLink', 'getHasLink'])
            ->getMock();

        $mockModel->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->method('getHasTextLink')
            ->willReturn(true);

        $mockModel->method('getHasLink')
            ->willReturn(false);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);


        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::WEEK);

        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->willReturn(false);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }


    public function test_message_has_message_link_should_returns_true()
    {
        $this->mockBotCreate();
        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFromAdmin', 'getHasTextLink', 'getHasLink'])
            ->getMock();

        $mockModel->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->method('getHasTextLink')
            ->willReturn(false);

        $mockModel->method('getHasLink')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::WEEK);

        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->willReturn(false);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }


    /**
     * ifMessageHasLinkBlockUser method test
     * @return void
     */
    public function test_not_message_model_instance_should_returns_false()
    {
        $this->mockBotCreate();
        $mockModel = $this->getMockBuilder(NewMemberJoinUpdateModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $this->assertFalse((new ChatRulesService($this->mockBotService))->ifMessageHasLinkBlockUser());
    }

    public function testReturnsFalseByDefault()
    {
        $this->mockBotCreate();
        $this->mockBotService->expects($this->never())
            ->method('banUser');

        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFromAdmin', 'getHasTextLink', 'getHasLink'])
            ->getMock();


        $mockModel->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->method('getHasTextLink')
            ->willReturn(false);

        $mockModel->method('getHasLink')
            ->willReturn(false);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::WEEK);

        //Link not found and assert that it returns false even if shouldRestrictUser is true
        $this->assertFalse($chatRulesService->ifMessageHasLinkBlockUser());
    }


    public function testShouldReturnsFalseIfRestrictUserColumnIsFalse()
    {
        $this->mockBotCreate();
        $this->chat->linksFilter()->first()->update(['restrict_user' => 0]);

        $this->mockBotService->expects($this->once())
            ->method('getChat')
            ->willReturn($this->chat);

        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $this->assertFalse((new ChatRulesService($this->mockBotService))->ifMessageHasLinkBlockUser());
    }

    public function testfMessageHasLinkAndResUserColumnIsTrueBanUserWIthRestrictionTimeFromDb()
    {
        $this->mockBotCreate();
        $this->chat->linksFilter()->first()->update(['restrict_user' => 1]);
        $this->chat->linksFilter()->first()->update(['restriction_time' => ResTime::MONTH]);

        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($this->chat);


        $this->mockBotService->expects($this->once())
            ->method('banUser')
            ->with(ResTime::MONTH);

        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHasTextLink', 'getHasLink', 'getFromAdmin'])
            ->getMock();

        $mockModel->expects($this->once())
            ->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->expects($this->once())
            ->method('getHasTextLink')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $this->assertTrue((new ChatRulesService($this->mockBotService))->ifMessageHasLinkBlockUser());
    }

    public function testDeleteMessageIfNeeds()
    {
        $this->mockBotCreate();
        $this->mockBotService->expects($this->once())
            ->method('deleteMessage');

        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHasTextLink', 'getFromAdmin'])
            ->getMock();


        $mockModel->expects($this->once())
            ->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->expects($this->once())
            ->method('getHasTextLink')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::WEEK);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->willReturn(true);

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }


    public function testNotDeleteMessageIfDeleteMessageColumnIsFalse()
    {
        $this->mockBotCreate();
        //Never
        $this->mockBotService->expects($this->never())
            ->method('deleteMessage');


        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHasTextLink', 'getFromAdmin'])
            ->getMock();

        //Skip admin check
        $mockModel->expects($this->once())
            ->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->expects($this->once())
            ->method('getHasTextLink')
            ->willReturn(true);

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::WEEK);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->willReturn(false);

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }


    public function testShouldDeleteMessageMethodReturnsTrueIfDeleteMessageColumnIsTrue()
    {
        $this->mockBotCreate();

        //Assert that the message should be deleted
        $this->mockBotService->expects($this->once())
            ->method('deleteMessage');

        // Test with a real chat model
        $this->chat->linksFilter()->first()->update(['delete_message' => 1]);
        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($this->chat);

        $mockRequestModel = $this->getMockModelWithTextLink();

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockRequestModel);

        //Do not mock shouldDeleteMessage method and test the real one
        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::DAY);

        $this->mockBotService->expects($this->once())
            ->method('banUser');


        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());

    }

    public function testShouldDeleteMessageMethodReturnsFalseIfDeleteMessageColumnIsFalse()
    {
        $this->mockBotCreate();
        //Should never call deleteMessage 
        $this->mockBotService->expects($this->never())
            ->method('deleteMessage');

        // Test with a real chat model
        $this->chat->linksFilter()->first()->update(['delete_message' => 0]);
        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($this->chat);

        $mockRequestModel = $this->getMockModelWithTextLink();

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockRequestModel);

        //Do not mock shouldDeleteMessage method and test the real one
        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::DAY);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        //Expected return value is still true, only test that the method does not call deleteMessage
        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());

    }


    public function testShouldDeleteMessageMethodExpectedArgument()
    {
        $this->mockBotCreate();
        //Never
        $this->mockBotService->expects($this->once())
            ->method('deleteMessage');

        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($this->chat);

        $mockRequestModel = $this->getMockModelWithTextLink();

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockRequestModel);

        //Mock also the shouldDeleteMessage method to test passed argument
        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::DAY);

        $this->mockBotService->expects($this->once())
            ->method('banUser');

        // Expected argument
        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->with('linksFilter')
            ->willReturn(true);


        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());

    }


    public function testBanReasonShouldBeSetToLinksFilter()
    {
        $this->mockBotCreate();

        $this->mockBotService->expects($this->once())
            ->method('deleteMessage');

        $this->mockBotService->expects($this->once())
            ->method('setBanReasonModelName')
            ->with('linksFilter');

        $this->mockBotService->expects($this->any())
            ->method('getChat')
            ->willReturn($this->chat);

        $mockRequestModel = $this->getMockModelWithTextLink();

        $this->mockBotService->expects($this->once())
            ->method('getRequestModel')
            ->willReturn($mockRequestModel);


        $chatRulesService = $this->getMockBuilder(ChatRulesService::class)
            ->setConstructorArgs([$this->mockBotService])
            ->onlyMethods(['shouldRestrictUser', 'getRestrictionTime', 'shouldDeleteMessage'])
            ->getMock();

        $chatRulesService->expects($this->once())
            ->method('shouldRestrictUser')
            ->willReturn(true);

        $chatRulesService->expects($this->once())
            ->method('getRestrictionTime')
            ->willReturn(ResTime::DAY);


        // Expected argument
        $chatRulesService->expects($this->once())
            ->method('shouldDeleteMessage')
            ->with('linksFilter')
            ->willReturn(true);


        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());

    }


    private function getMockModelWithTextLink()
    {
        $mockModel = $this->getMockBuilder(TextMessageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHasTextLink', 'getFromAdmin'])
            ->getMock();

        //Skip admin check
        $mockModel->expects($this->once())
            ->method('getFromAdmin')
            ->willReturn(false);

        $mockModel->expects($this->once())
            ->method('getHasTextLink')
            ->willReturn(true);


        return $mockModel;
    }

}