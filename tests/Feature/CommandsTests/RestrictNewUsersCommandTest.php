<?php

namespace Tests\Feature;

use App\Enums\ModerationSettingsEnum;
use App\Enums\ResNewUsersEnum;
use App\Models\NewUserRestriction;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\ResTime;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Classes\PrivateChatCommandCore;
use Database\Seeders\SimpleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class RestrictNewUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    // protected NewUserRestriction $restrictions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->chat = Chat::first();
        $this->restrictions = $this->chat->newUserRestrictions;
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $this->setCommand(ResNewUsersEnum::EDIT_RESTRICTIONS->value);
        $this->prepareDependencies();
        (new PrivateChatCommandCore());

        $buttons = $this->getEditRestrictionsButtons($this->restrictions, ResNewUsersEnum::class);
        $this->assertButtonsWereSent($buttons);
        $this->assertBackToPreviousMenuButtonWasSent();
        $this->assertBackMenuArrayContains(ResNewUsersEnum::EDIT_RESTRICTIONS->value);
    }

    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->setCommand(ResNewUsersEnum::SELECT_RESTRICTION_TIME->value);
        $this->prepareDependencies();

        (new PrivateChatCommandCore());

        $buttons = [
            ResNewUsersEnum::SET_TIME_DAY->value,
            ResNewUsersEnum::SET_TIME_TWO_HOURS->value,
            ResNewUsersEnum::SET_TIME_WEEK->value,
            ResNewUsersEnum::SET_TIME_MONTH->value
        ];
        $this->assertBackMenuArrayContains(ResNewUsersEnum::SELECT_RESTRICTION_TIME->value);
        $this->assertButtonsWereSent($buttons);
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->setCommand(ResNewUsersEnum::SET_TIME_MONTH->value);
        $this->prepareDependencies();

        $this->setAllRestrictionsToFalse($this->chat);
        $this->restrictions->refresh();
        $this->assertEquals(0, $this->restrictions->enabled);

        (new PrivateChatCommandCore());

        $this->restrictions->refresh();
        $this->assertEquals(1, $this->restrictions->enabled);
        $this->assertEquals(ResTime::MONTH->value, $this->restrictions->first()->restriction_time);
        $this->assertReplyMessageSent(ResNewUsersEnum::SET_TIME_MONTH->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->setCommand(ResNewUsersEnum::RESTRICTIONS_ENABLE_ALL->value);
        $this->prepareDependencies();
        $this->setAllRestrictionsDisabled($this->chat);
        $lastRestrictionTime = $this->chat->newUserRestrictions->restriction_time;

        //Setting only 'restrict_new_users' to 0 before test
        (new PrivateChatCommandCore());

        $this->assertEquals(1, $this->restrictions->first()->enabled);
        $this->assertEquals($lastRestrictionTime, $this->restrictions->first()->restriction_time);
        $this->assertReplyMessageSent(ResNewUsersEnum::RESTRICTIONS_ENABLE_ALL->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->setCommand(ResNewUsersEnum::RESTRICTIONS_DISABLE_ALL->value);
        $this->prepareDependencies();
        //Setting everything to 0 before test
        $this->setAllRestrictionsEnabled($this->chat);

        (new PrivateChatCommandCore());
        $this->assertEquals(0, $this->restrictions->first()->enabled);
        $this->assertReplyMessageSent(ResNewUsersEnum::RESTRICTIONS_DISABLE_ALL->replyMessage());
    }


    private function prepareDependencies()
    {
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->chat->chat_id);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }


    public function testNewUsersEnableSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::SEND_MEDIA_ENABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_media" => 0
        ]);

        (new PrivateChatCommandCore());
        $this->assertEquals(1, $this->restrictions->first()->can_send_media);
        $this->assertReplyMessageSent(ResNewUsersEnum::SEND_MEDIA_ENABLE->replyMessage());
    }

    public function testNewUsersDisableSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::SEND_MEDIA_DISABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_media" => 1
        ]);
        (new PrivateChatCommandCore());

        $this->assertEquals(0, $this->restrictions->first()->can_send_media);
        $this->assertReplyMessageSent(ResNewUsersEnum::SEND_MEDIA_DISABLE->replyMessage());
    }

    public function testNewUsersEnableSendMessages()
    {
        $this->setCommand(ResNewUsersEnum::SEND_MESSAGES_ENABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_messages" => 0
        ]);

        (new PrivateChatCommandCore());
        $this->assertEquals(1, $this->restrictions->first()->can_send_messages);
        $this->assertReplyMessageSent(ResNewUsersEnum::SEND_MESSAGES_ENABLE->replyMessage());
    }

    public function testNewUsersDisableSendMessages()
    {
        $this->setCommand(ResNewUsersEnum::SEND_MESSAGES_DISABLE->value);
        $this->prepareDependencies();
        $this->restrictions->update([
            "can_send_messages" => 1
        ]);
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->chat->chat_id);

        (new PrivateChatCommandCore());
        $this->assertEquals(0, $this->restrictions->first()->can_send_messages);
        $this->assertReplyMessageSent(ResNewUsersEnum::SEND_MESSAGES_DISABLE->replyMessage());
    }

}
