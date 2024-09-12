<?php

namespace Tests\Feature\Middleware;

use App\Classes\ChatRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Services\CONSTANTS;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class ChatRulesTest extends TestCase
{
    use RefreshDatabase;
    use MockBotService;



    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats->first();
        $this->mockBotCreate();
    }

    public function testBlockNewVisitorReturnsTrue()
    {
        $ruleService = $this->getMockBuilder(ChatRulesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleService->expects($this->once())
            ->method("blockNewVisitor")
            ->willReturn(true);

        $result = (new ChatRules($this->mockBotService, $ruleService))->validate();
        $this->assertEquals(CONSTANTS::NEW_MEMBER_RESTRICTED, $result->getContent());
    }


    public function testBlockUserIfMessageIsForwardReturnsTrue()
    {
        $ruleService = $this->getMockBuilder(ChatRulesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleService->expects($this->once())
            ->method("BlockUserIfMessageIsForward")
            ->willReturn(true);

        $result = (new ChatRules($this->mockBotService, $ruleService))->validate();
        $this->assertEquals(CONSTANTS::MEMBER_BLOCKED, $result->getContent());
    }

    public function testIfMessageHasLinkBlockUserReturnsTrue()
    {
        $ruleService = $this->getMockBuilder(ChatRulesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleService->expects($this->once())
            ->method("ifMessageHasLinkBlockUser")
            ->willReturn(true);

        $result = (new ChatRules($this->mockBotService, $ruleService))->validate();
        $this->assertEquals(CONSTANTS::MEMBER_BLOCKED, $result->getContent());
    }


    public function testIfMessageContainsBlackListWordsBanUserReturnsTrue()
    {
        $ruleService = $this->getMockBuilder(ChatRulesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleService->expects($this->once())
            ->method("ifMessageContainsBlackListWordsBanUser")
            ->willReturn(true);

        $result = (new ChatRules($this->mockBotService, $ruleService))->validate();
        $this->assertEquals(CONSTANTS::DELETED_BY_FILTER, $result->getContent());
    }


    public function testIfNothingReturnsDefaultResponse()
    {
        $ruleService = $this->getMockBuilder(ChatRulesService::class)
            ->disableOriginalConstructor()
            ->getMock();


        $result = (new ChatRules($this->mockBotService, $ruleService))->validate();
        $this->assertEquals(CONSTANTS::DEFAULT_RESPONSE, $result->getContent());
    }

}