<?php

namespace Tests\Feature;

use App\Classes\Buttons;
use App\Classes\Commands\NewUserRestrictionsCommand;
use App\Enums\CommandEnums\BadWordsFilterEnum;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\CommandEnums\NewUserRestrictionsEnum;
use App\Classes\PrivateChatCommandCore;
use App\Enums\ResTime;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class NewUserRestrictionsCommandTest extends TestCase
{
    use RefreshDatabase;
    use MockBotService;

    // protected NewUserRestriction $restrictions;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->fakeSendMessageSucceedResponse();
        // $this->setPrivateChatBotService(1, 2);
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats()->first();
        $this->restrictions = $this->chat->newUserRestrictions;
        // $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        // $this->clearTestLogFile();
        $this->mockBotCreate();
        $this->mockBotGetChatMethod($this->chat);
        $this->mockBotGetAdminMethod($this->admin);
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::EDIT_RESTRICTIONS->value);
        $buttons = (new Buttons())->getEditRestrictionsButtons($this->restrictions, NewUserRestrictionsEnum::class);
        $this->expectReplyMessage(NewUserRestrictionsEnum::EDIT_RESTRICTIONS->replyMessage(), $buttons);
        $this->expectMockMenuMethod("save", 1);
        $this->mockBotMenuCreate();
        new NewUserRestrictionsCommand($this->mockBotService);
    }


    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::SELECT_RESTRICTION_TIME->value);
        $this->mockBotMenuCreate();
        $buttons = (new Buttons())->getEditRestrictionsTimeButtons($this->restrictions, NewUserRestrictionsEnum::class);

        $this->expectReplyMessage(NewUserRestrictionsEnum::SELECT_RESTRICTION_TIME->replyMessage(), $buttons);
        $this->assertTrue(true); // just to pass the test
        new NewUserRestrictionsCommand($this->mockBotService);
    }

    public function testDisableNewUsersRestrictions()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::ENABLED_DISABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(NewUserRestrictionsEnum::ENABLED_DISABLE->replyMessage());
        $this->restrictions->update(['enabled' => 1]); //set to enabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->enabled === 0);
    }


    public function testEnableNewUsersRestrictions()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::ENABLED_ENABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(NewUserRestrictionsEnum::ENABLED_ENABLE->replyMessage());
        $this->restrictions->update(['enabled' => 0]); //set to enabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->enabled === 1);
    }

    public function testSetRestrictNewUsersTimeMonth()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::SET_TIME_MONTH->value);
        $this->mockBotMenuCreate();

        $this->restrictions->update(['restriction_time' => 0]); //set to disabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->restriction_time === ResTime::getTime(NewUserRestrictionsEnum::SET_TIME_MONTH));
    }


    public function testSetBadWordsFilterRestrictionTimeWeek()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::SET_TIME_WEEK->value);
        $this->mockBotMenuCreate();

        $this->restrictions->update(['restriction_time' => 0]); //set to disabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->restriction_time === ResTime::getTime(NewUserRestrictionsEnum::SET_TIME_WEEK));
    }


    public function testSetBadWordsFilterRestrictionTimeDay()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::SET_TIME_DAY->value);
        $this->mockBotMenuCreate();

        $this->restrictions->update(['restriction_time' => 0]); //set to disabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->restriction_time === ResTime::getTime(NewUserRestrictionsEnum::SET_TIME_DAY));
    }


    public function testSetBadWordsFilterRestrictionTimeTwoHours()
    {
        $this->mockBotCommand(NewUserRestrictionsEnum::SET_TIME_TWO_HOURS->value);
        $this->mockBotMenuCreate();

        $this->restrictions->update(['restriction_time' => 0]); //set to disabled before test

        new NewUserRestrictionsCommand($this->mockBotService);
        $this->assertTrue($this->restrictions->restriction_time === ResTime::getTime(NewUserRestrictionsEnum::SET_TIME_TWO_HOURS));
    }
}

