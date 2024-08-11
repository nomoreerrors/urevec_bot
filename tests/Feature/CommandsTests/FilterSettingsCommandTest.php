<?php

namespace Feature\CommandsTests;

use App\Classes\PrivateChatCommandCore;
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
use App\Enums\BadWordsFilterCmd;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class FilterSettingsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->chat = $this->admin->chats->first();
        $this->fakeSendMessageSucceedResponse();
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }

    public function testifSelectBadWordsFilterReplyWithBadWordsSettingsButtons()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SETTINGS->value);
        $this->prepareDependencies();

        $isEnabled = $this->chat->badWordsFilter->filter_enabled === 1;
        $deleteMessagesEnabled = $this->chat->badWordsFilter->delete_message === 1;
        $restrictUsersEnabled = $this->chat->badWordsFilter->restrict_user === 1;

        $toggleFIlter = $isEnabled ?
            BadWordsFilterCmd::BAD_WORDS_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_ENABLE->value;

        $toggleDeleteMessage = $deleteMessagesEnabled ?
            BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->value;

        $toggleRestrictUser = $restrictUsersEnabled ?
            BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->value;

        $restrictTime = BadWordsFilterCmd::SELECT_TIME->value;

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString($toggleFIlter, $sendMessageLog);
        $this->assertStringContainsString($toggleDeleteMessage, $sendMessageLog);
        $this->assertStringContainsString($toggleRestrictUser, $sendMessageLog);
        $this->assertStringContainsString($restrictTime, $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SETTINGS->replyMessage(), $sendMessageLog);
    }


    public function testDisableBadWordsFilter()
    {
        //DISABLE
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_DISABLE->value);
        $this->chat->badWordsFilter()->update(['filter_enabled' => 1]); //set to enabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_DISABLE->replyMessage(), $sendMessageLog);
        $this->assertFalse($this->chat->badWordsFilter->filter_enabled === 1);
    }


    public function testEnableBadWordsFilter()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_ENABLE->value);
        $this->chat->badWordsFilter()->update(['filter_enabled' => 0]); //set to disabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_ENABLE->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->filter_enabled === 1);
    }


    public function testDisableBadWordsFilterDeleteMessages()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_DISABLE->value);
        $this->chat->badWordsFilter()->update(['delete_message' => 1]); //set to enabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_DISABLE->replyMessage(), $sendMessageLog);
        $this->assertFalse($this->chat->badWordsFilter->delete_message === 1);
    }


    public function testEnableBadWordsFilterDeleteMessages()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->value);
        $this->chat->badWordsFilter()->update(['delete_message' => 0]); //set to disabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->delete_message === 1);
    }


    public function testDisableBadWordsFilterRestrictions()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_DISABLE->value);
        $this->chat->badWordsFilter()->update(['restrict_user' => 1]); //set to enabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_DISABLE->replyMessage(), $sendMessageLog);
        $this->assertFalse($this->chat->badWordsFilter->restrict_user === 1);
    }


    public function testEnableBadWordsFilterRestrictions()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->value);
        $this->chat->badWordsFilter()->update(['delete_message' => 0]); //set to disabled before test

        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->restrict_user === 1);
    }


    public function testSelectBadWordsFilterRestrictionTimeSettingsReplyWithButtons()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SELECT_TIME->value);
        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::SELECT_TIME->replyMessage(), $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_MONTH->value, $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_DAY->value, $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_TWO_HOURS->value, $sendMessageLog);
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_WEEK->value, $sendMessageLog);
    }


    public function testSetBadWordsFilterRestrictionTimeMonth()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SET_TIME_MONTH->value);
        $this->chat->badWordsFilter()->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_MONTH->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->restriction_time === ResTime::getTime(BadWordsFilterCmd::SET_TIME_MONTH));
    }


    public function testSetBadWordsFilterRestrictionTimeWeek()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SET_TIME_WEEK->value);
        $this->chat->badWordsFilter()->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_WEEK->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->restriction_time === ResTime::getTime(BadWordsFilterCmd::SET_TIME_WEEK));
    }


    public function testSetBadWordsFilterRestrictionTimeDay()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SET_TIME_DAY->value);
        $this->chat->badWordsFilter()->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_DAY->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->restriction_time === ResTime::getTime(BadWordsFilterCmd::SET_TIME_DAY));
    }


    public function testSetBadWordsFilterRestrictionTimeTwoHours()
    {
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id, BadWordsFilterCmd::SET_TIME_TWO_HOURS->value);
        $this->chat->badWordsFilter()->update(['restriction_time' => 0]); //set to disabled before test
        $this->prepareDependencies();

        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(BadWordsFilterCmd::SET_TIME_TWO_HOURS->replyMessage(), $sendMessageLog);
        $this->assertTrue($this->chat->badWordsFilter->restriction_time === ResTime::getTime(BadWordsFilterCmd::SET_TIME_TWO_HOURS));
    }


    public function prepareDependencies()
    {
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->requestModel);
        app()->singleton("requestModel", fn() => $this->requestModel);
        app()->singleton("botService", fn() => $this->botService);
        // fake that chat was previously selected
        $this->fakeChatSelected(
            $this->admin->admin_id,
            $this->admin->chats->first()->chat_id
        );
        new PrivateChatCommandCore();
    }
}


