<?php

namespace Tests\Feature;

use App\Enums\ModerationSettingsEnum;
use App\Enums\NewUserRestrictionsEnum;
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
        $this->setPrivateChatBotService(1, 2);
        $this->restrictions = $this->chat->newUserRestrictions;
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $this->setCommand(NewUserRestrictionsEnum::EDIT_RESTRICTIONS->value);
        $this->prepareDependencies();
        (new PrivateChatCommandCore());

        $buttons = $this->getEditRestrictionsButtons($this->restrictions, NewUserRestrictionsEnum::class);
        $this->assertButtonsWereSent($buttons);
        $this->assertBackToPreviousMenuButtonWasSent();
        $this->assertJsonBackMenuArrayContains(NewUserRestrictionsEnum::EDIT_RESTRICTIONS->value);
    }

    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->setCommand(NewUserRestrictionsEnum::SELECT_RESTRICTION_TIME->value);
        $this->prepareDependencies();

        (new PrivateChatCommandCore());

        $buttons = [
            NewUserRestrictionsEnum::SET_TIME_DAY->value,
            NewUserRestrictionsEnum::SET_TIME_TWO_HOURS->value,
            NewUserRestrictionsEnum::SET_TIME_WEEK->value,
            NewUserRestrictionsEnum::SET_TIME_MONTH->value
        ];
        $this->assertJsonBackMenuArrayContains(NewUserRestrictionsEnum::SELECT_RESTRICTION_TIME->value);
        $this->assertButtonsWereSent($buttons);
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::SET_TIME_MONTH->value);
        $this->prepareDependencies();

        $this->setAllRestrictionsToFalse($this->chat);
        $this->restrictions->refresh();
        $this->assertEquals(0, $this->restrictions->enabled);

        (new PrivateChatCommandCore());

        $this->restrictions->refresh();
        $this->assertEquals(1, $this->restrictions->enabled);
        $this->assertEquals(ResTime::MONTH->value, $this->restrictions->first()->restriction_time);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::SET_TIME_MONTH->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->setBackMenuArrayToCache(["one", "two"]); // so that the refresh() method works correctly
        $this->setCommand(NewUserRestrictionsEnum::ENABLE->value);
        $this->prepareDependencies();
        $this->setAllRestrictionsDisabled($this->chat);
        $lastRestrictionTime = $this->chat->newUserRestrictions->restriction_time;

        //Setting only 'restrict_new_users' to 0 before test
        (new PrivateChatCommandCore());

        $this->assertEquals(1, $this->restrictions->first()->enabled);
        $this->assertEquals($lastRestrictionTime, $this->restrictions->first()->restriction_time);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::ENABLE->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::DISABLE->value);
        $this->prepareDependencies();
        //Setting everything to 0 before test
        $this->setAllRestrictionsEnabled($this->chat);

        (new PrivateChatCommandCore());
        $this->assertEquals(0, $this->restrictions->first()->enabled);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::DISABLE->replyMessage());
    }


    private function prepareDependencies()
    {
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);

        app()->singleton("requestModel", fn() => $this->model);
        app()->singleton("botService", fn() => $this->botService);
    }


    public function testToggleNewUsersEnableSendMedia()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::SEND_MEDIA_ENABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_media" => 0
        ]);

        (new PrivateChatCommandCore());
        $this->assertEquals(1, $this->restrictions->first()->can_send_media);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::SEND_MEDIA_ENABLE->replyMessage());
    }

    public function testNewUsersDisableSendMedia()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::SEND_MEDIA_DISABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_media" => 1
        ]);
        (new PrivateChatCommandCore());

        $this->assertEquals(0, $this->restrictions->first()->can_send_media);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::SEND_MEDIA_DISABLE->replyMessage());
    }

    public function testNewUsersEnableSendMessages()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::SEND_MESSAGES_ENABLE->value);
        $this->prepareDependencies();

        $this->restrictions->update([
            "can_send_messages" => 0
        ]);

        (new PrivateChatCommandCore());
        $this->assertEquals(1, $this->restrictions->first()->can_send_messages);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::SEND_MESSAGES_ENABLE->replyMessage());
    }

    public function testNewUsersDisableSendMessages()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setCommand(NewUserRestrictionsEnum::SEND_MESSAGES_DISABLE->value);
        $this->prepareDependencies();
        $this->restrictions->update([
            "can_send_messages" => 1
        ]);
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);

        (new PrivateChatCommandCore());
        $this->assertEquals(0, $this->restrictions->first()->can_send_messages);
        $this->assertReplyMessageSent(NewUserRestrictionsEnum::SEND_MESSAGES_DISABLE->replyMessage());
    }

    public function testChangesAppliesToCurrentlySelectedChat()
    {
        $this->setBackMenuArrayToCache(["one", "two"]);
        $this->setAllRestrictionsToFalse($this->chat);
        $this->putSelectedChatIdToCache($this->admin->admin_id, $this->chat->chat_id);

        app("botService")->setPrivateChatCommand(NewUserRestrictionsEnum::ENABLE->value);


        new PrivateChatCommandCore();
        $this->assertEquals(1, app("botService")->getChat()->newUserRestrictions()->first()->fresh()->enabled);
        $this->assertEquals($this->chat->chat_id, app("botService")->getChat()->chat_id);



        $secondChat = $this->admin->chats->where("chat_id", "!=", $this->chat->chat_id)->first();
        $this->selectChatAndAssert($secondChat->chat_title, $secondChat->chat_id);
        $this->assertLastChatIdWasCached($this->admin->admin_id, $secondChat->chat_id);
        $this->setAllRestrictionsToFalse($secondChat);

        app("botService")->setPrivateChatCommand(NewUserRestrictionsEnum::SEND_MEDIA_ENABLE->value);

        new PrivateChatCommandCore();
        $this->assertEquals(1, app("botService")->getChat()->newUserRestrictions()->first()->fresh()->can_send_media);

        $this->deleteSelectedChatFromCache($this->admin->admin_id);
    }

}
