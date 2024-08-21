<?php

namespace Feature\CommandsTests;

use App\Classes\PrivateChatCommandCore;
use App\Enums\ModerationSettingsEnum;
use App\Models\MessageModels\TextMessageModel;
use App\Services\TelegramBotService;
use App\Enums\ResTime;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\BadWordsFilterEnum;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class BadWordsFilterCommandTest extends TestCase
{
    protected $filter;
    public function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->chat = $this->admin->chats->first();
        $this->filter = $this->chat->badWordsFilter;
        $this->fakeSendMessageSucceedResponse();
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }

    public function testifSelectBadWordsFilterEditRestrictionsReplyWithButtons()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::EDIT_RESTRICTIONS->value);
        $this->prepareDependencies();

        $buttons = $this->getEditRestrictionsButtons($this->filter, BadWordsFilterEnum::class);
        $buttons[] = ModerationSettingsEnum::BACK->value;

        $this->assertButtonsWereSent($buttons);
        $this->assertReplyMessageSent(BadWordsFilterEnum::EDIT_RESTRICTIONS->replyMessage());
    }


    public function testifSelectBadWordsFilterSettingsReplyWithButtons()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SETTINGS->value);
        $this->prepareDependencies();
        $buttons = $this->getFilterSettingsButtons($this->filter, BadWordsFilterEnum::class);

        $this->assertButtonsWereSent($buttons);
        $this->assertReplyMessageSent(BadWordsFilterEnum::SETTINGS->replyMessage());
    }


    public function testDisableBadWordsFilter()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::DISABLE->value);
        $this->filter->update(['enabled' => 1]); //set to enabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::DISABLE->replyMessage());
        $this->assertFalse($this->filter->enabled === 1);
    }


    public function testEnableBadWordsFilter()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::ENABLE->value);
        $this->filter->update(['enabled' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::ENABLE->replyMessage());
        $this->assertTrue($this->filter->enabled === 1);
    }


    public function testDisableBadWordsFilterDeleteMessages()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::DELETE_MESSAGES_DISABLE->value);
        $this->filter->update(['delete_message' => 1]); //set to enabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::DELETE_MESSAGES_DISABLE->replyMessage());
        $this->assertFalse($this->filter->delete_message === 1);
    }


    public function testEnableBadWordsFilterDeleteMessages()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::DELETE_MESSAGES_ENABLE->value);
        $this->filter->update(['delete_message' => 0]); //set to disabled before test

        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::DELETE_MESSAGES_ENABLE->replyMessage());
        $this->assertTrue($this->filter->delete_message === 1);
    }


    public function testEnableBadWordsFilterRestrictions()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::RESTRICTIONS_ENABLE_ALL->value);
        $this->filter->update(['enabled' => 0]); //set to disabled before test


        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::RESTRICTIONS_ENABLE_ALL->replyMessage());
        $this->assertTrue($this->filter->enabled === 1);
    }


    public function testSelectBadWordsFilterRestrictionTimeSettingsReplyWithButtons()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SELECT_RESTRICTION_TIME->value);
        $this->prepareDependencies();
        $this->assertReplyMessageSent(BadWordsFilterEnum::SELECT_RESTRICTION_TIME->replyMessage());
        $this->assertButtonsWereSent($this->getRestrictionsTimeButtons($this->filter, BadWordsFilterEnum::class));
    }


    public function testSetBadWordsFilterRestrictionTimeMonth()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SET_TIME_MONTH->value);
        $this->filter->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::SET_TIME_MONTH->replyMessage());
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_MONTH));
    }


    public function testSetBadWordsFilterRestrictionTimeWeek()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SET_TIME_WEEK->value);
        $this->filter->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::SET_TIME_WEEK->replyMessage());
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_WEEK));
    }


    public function testSetBadWordsFilterRestrictionTimeDay()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SET_TIME_DAY->value);
        $this->filter->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::SET_TIME_DAY->replyMessage());
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_DAY));
    }


    public function testSetBadWordsFilterRestrictionTimeTwoHours()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterEnum::SET_TIME_TWO_HOURS->value);
        $this->filter->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $this->filter->refresh();
        $this->assertReplyMessageSent(BadWordsFilterEnum::SET_TIME_TWO_HOURS->replyMessage());
        $this->assertTrue($this->filter->restriction_time === ResTime::getTime(BadWordsFilterEnum::SET_TIME_TWO_HOURS));
    }


    public function prepareDependencies()
    {
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->requestModel);
        app()->singleton("requestModel", fn() => $this->requestModel);
        app()->singleton("botService", fn() => $this->botService);
        // fake that chat was previously selected
        $this->putSelectedChatIdToCache(
            $this->admin->admin_id,
            $this->admin->chats->first()->chat_id
        );
        new PrivateChatCommandCore();
    }

}


