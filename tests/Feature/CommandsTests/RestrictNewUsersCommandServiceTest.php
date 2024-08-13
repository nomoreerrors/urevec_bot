<?php

namespace Tests\Feature;

use App\Enums\MainMenuCmd;
use App\Enums\ResNewUsersEnum;
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

class RestrictNewUsersCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->data = $this->getPrivateChatMessage($this->admin->admin_id);
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
        $this->clearTestLogFile();
    }


    public function testSelectNewUsersRestrictionsReplyWithButtons()
    {
        $this->setCommand(ResNewUsersEnum::SETTINGS->value);
        $this->prepareDependencies();
        (new PrivateChatCommandCore());

        $buttons = $this->getNewUsersRestrictionsButtons();
        $this->assertButtonsWereSent($buttons);
        $this->assertBackToPreviousMenuButtonWasSent();
        $this->assertBackMenuArrayContains(ResNewUsersEnum::SETTINGS->value);
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

        $this->assertButtonsWereSent($buttons);
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->setCommand(ResNewUsersEnum::SET_TIME_MONTH->value);
        $this->prepareDependencies();

        $this->setAllRestrictionsToFalse($this->chat);
        $this->assertEquals(0, $this->chat->newUserRestrictions->restrict_new_users);

        (new PrivateChatCommandCore());

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(ResTime::MONTH->value, $this->chat->newUserRestrictions()->first()->restriction_time);
        $this->assertReplyMessageSent(ResNewUsersEnum::SET_TIME_MONTH->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->setCommand(ResNewUsersEnum::ENABLE_ALL->value);
        $this->prepareDependencies();
        $this->setAllRestrictionsDisabled($this->chat);
        $lastRestrictionTime = $this->chat->newUserRestrictions->restriction_time;

        //Setting only 'restrict_new_users' to 0 before test
        (new PrivateChatCommandCore());

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_messages);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertEquals($lastRestrictionTime, $this->chat->newUserRestrictions()->first()->restriction_time);
        $this->assertReplyMessageSent(ResNewUsersEnum::ENABLE_ALL->replyMessage());
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->setCommand(ResNewUsersEnum::DISABLE_ALL->value);
        $this->prepareDependencies();
        //Setting everything to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandCore());
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertReplyMessageSent(ResNewUsersEnum::DISABLE_ALL->replyMessage());
    }



    public function testEnableNewUsersCanSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::ENABLE_SEND_MEDIA->value);
        $this->prepareDependencies();

        //Disable sending media for users before test
        $this->chat->newUserRestrictions->update([
            "can_send_media" => 0
        ]);

        (new PrivateChatCommandCore());
        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertReplyMessageSent(ResNewUsersEnum::ENABLE_SEND_MEDIA->replyMessage());
    }

    public function testDisableNewUsersCanSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::DISABLE_SEND_MEDIA->value);
        $this->prepareDependencies();

        $this->chat->newUserRestrictions->update([
            "restrict_new_users" => 0, // Disable restrictions to make sure that it'll be enabled too
            "can_send_media" => 1 // Enable sending media
        ]);

        (new PrivateChatCommandCore());

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertReplyMessageSent(ResNewUsersEnum::DISABLE_SEND_MEDIA->replyMessage());
    }


    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->chat->chat_id);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }


    public function testNewUsersEnableSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::ENABLE_SEND_MEDIA->value);
        $this->prepareDependencies();

        (new PrivateChatCommandCore());
        $this->assertReplyMessageSent(ResNewUsersEnum::ENABLE_SEND_MEDIA->replyMessage());
    }

    public function testNewUsersDisableSendMedia()
    {
        $this->setCommand(ResNewUsersEnum::DISABLE_SEND_MEDIA->value);
        $this->prepareDependencies();
        (new PrivateChatCommandCore());
        $this->assertReplyMessageSent(ResNewUsersEnum::DISABLE_SEND_MEDIA->replyMessage());
    }

    public function testNewUsersEnableSendMessages()
    {
        $this->setCommand(ResNewUsersEnum::ENABLE_SEND_MESSAGES->value);
        $this->prepareDependencies();
        (new PrivateChatCommandCore());
        $this->assertReplyMessageSent(ResNewUsersEnum::ENABLE_SEND_MESSAGES->replyMessage());
    }

    public function testNewUsersDisableSendMessages()
    {
        $this->setCommand(ResNewUsersEnum::DISABLE_SEND_MESSAGES->value);
        $this->prepareDependencies();
        $this->fakeThatChatWasSelected($this->admin->admin_id, $this->chat->chat_id);

        (new PrivateChatCommandCore());
        $this->assertReplyMessageSent(ResNewUsersEnum::DISABLE_SEND_MESSAGES->replyMessage());
    }

    public function getNewUsersRestrictionsButtons(): array
    {
        $canSendMessages = $this->chat->newUserRestrictions->can_send_messages;
        $canSendMedia = $this->chat->newUserRestrictions->can_send_media;
        $restrictNewUsers = $this->chat->newUserRestrictions->restrict_new_users;


        $buttons['canSendMessages'] = $canSendMessages === 1 ?
            ResNewUsersEnum::DISABLE_SEND_MESSAGES->value :
            ResNewUsersEnum::ENABLE_SEND_MESSAGES->value;
        $buttons['canSendMedia'] = $canSendMedia === 1 ?
            ResNewUsersEnum::DISABLE_SEND_MEDIA->value :
            ResNewUsersEnum::ENABLE_SEND_MEDIA->value;
        $buttons['restrictNewUsers'] = $restrictNewUsers === 1 ?
            ResNewUsersEnum::DISABLE_ALL->value :
            ResNewUsersEnum::ENABLE_ALL->value;
        $buttons['selectTime'] = ResNewUsersEnum::SELECT_RESTRICTION_TIME->value;
        return $buttons;
    }
}
