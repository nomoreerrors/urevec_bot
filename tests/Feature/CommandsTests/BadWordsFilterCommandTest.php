<?php

namespace Feature\CommandsTests;

use App\Classes\Commands\BadWordsFilterCommand;
use App\Classes\Buttons;
use App\Enums\CommandEnums\BadWordsFilterEnum;
use App\Enums\ResTime;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class BadWordsFilterCommandTest extends TestCase
{
    use MockBotService;
    protected $filter;
    public function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->setAdminWithMultipleChats(2);
        $this->chat = $this->admin->chats->first();
        $this->filter = $this->chat->badWordsFilter;
        $this->mockBotCreate();
        $this->mockBotGetChatMethod($this->admin->chats->first());
        $this->mockBotGetAdminMethod($this->admin);
        // $this->clearTestLogFile();
    }

    public function testifSelectBadWordsFilterEditRestrictionsReplyWithButtons()
    {
        $this->mockBotCommand(BadWordsFilterEnum::EDIT_RESTRICTIONS->value);

        $this->mockMenuCreate();
        $this->expectMockMenuMethod("save");
        $this->mockBotMenuCreate(1);

        $buttons = (new Buttons())->getEditRestrictionsButtons($this->filter, BadWordsFilterEnum::class);
        $this->expectReplyMessage(BadWordsFilterEnum::EDIT_RESTRICTIONS->replyMessage(), $buttons, 1);

        new BadWordsFilterCommand($this->mockBotService);
    }



    public function testDisableBadWordsFilter()
    {
        $this->mockBotCommand(BadWordsFilterEnum::ENABLED_DISABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::ENABLED_DISABLE->replyMessage());
        $this->filter->update(['enabled' => 1]); //set to enabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->enabled === 0);
    }


    public function testEnableBadWordsFilter()
    {
        $this->mockBotCommand(BadWordsFilterEnum::ENABLED_ENABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::ENABLED_ENABLE->replyMessage());
        $this->filter->update(['enabled' => 0]); //set to enabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->enabled === 1);
    }


    public function testDisableBadWordsFilterDeleteMessages()
    {
        $this->mockBotCommand(BadWordsFilterEnum::DELETE_MESSAGE_DISABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::DELETE_MESSAGE_DISABLE->replyMessage());

        $this->filter->update(['delete_message' => 1]); //set to enabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->delete_message === 0);
    }


    public function testEnableBadWordsFilterDeleteMessages()
    {
        $this->mockBotCommand(BadWordsFilterEnum::DELETE_MESSAGE_ENABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::DELETE_MESSAGE_ENABLE->replyMessage());

        $this->filter->update(['delete_message' => 0]); //set to enabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->delete_message === 1);
    }


    public function testEnableBadWordsFilterRestrictions()
    {
        $this->mockBotCommand(BadWordsFilterEnum::RESTRICT_USER_ENABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::RESTRICT_USER_ENABLE->replyMessage());

        $this->filter->update(['restrict_user' => 0]); //set to enabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restrict_user === 1);
    }

    public function testDisableBadWordsFilterRestrictions()
    {
        $this->mockBotCommand(BadWordsFilterEnum::RESTRICT_USER_DISABLE->value);
        $this->mockBotMenuCreate();
        $this->expectReplyMessage(BadWordsFilterEnum::RESTRICT_USER_DISABLE->replyMessage());

        $this->filter->update(['restrict_user' => 1]); //set to disabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restrict_user === 0);
    }


    public function testSelectBadWordsFilterRestrictionTimeSettingsReplyWithButtons()
    {
        $this->mockBotCommand(BadWordsFilterEnum::SELECT_RESTRICTION_TIME->value);
        $this->mockBotMenuCreate();
        $buttons = (new Buttons())->getEditRestrictionsTimeButtons($this->filter, BadWordsFilterEnum::class);

        $this->expectReplyMessage(BadWordsFilterEnum::SELECT_RESTRICTION_TIME->replyMessage(), $buttons);
        $this->assertTrue(true); // just to pass the test
        new BadWordsFilterCommand($this->mockBotService);
    }


    public function testSetBadWordsFilterRestrictionTimeMonth()
    {
        $this->mockBotCommand(BadWordsFilterEnum::SET_TIME_MONTH->value);
        $this->mockBotMenuCreate();

        $this->filter->update(['restriction_time' => 0]); //set to disabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_MONTH));
    }


    public function testSetBadWordsFilterRestrictionTimeWeek()
    {
        $this->mockBotCommand(BadWordsFilterEnum::SET_TIME_WEEK->value);
        $this->mockBotMenuCreate();

        $this->filter->update(['restriction_time' => 0]); //set to disabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_WEEK));
    }


    public function testSetBadWordsFilterRestrictionTimeDay()
    {
        $this->mockBotCommand(BadWordsFilterEnum::SET_TIME_DAY->value);
        $this->mockBotMenuCreate();

        $this->filter->update(['restriction_time' => 0]); //set to disabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_DAY));
    }


    public function testSetBadWordsFilterRestrictionTimeTwoHours()
    {
        $this->mockBotCommand(BadWordsFilterEnum::SET_TIME_TWO_HOURS->value);
        $this->mockBotMenuCreate();

        $this->filter->update(['restriction_time' => 0]); //set to disabled before test

        new BadWordsFilterCommand($this->mockBotService);
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_TWO_HOURS));
    }

}


