<?php

namespace Tests\Feature;

use App\Enums\MainMenuCmd;
use App\Enums\ResNewUsersCmd;
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
        $this->data["message"]["text"] = ResNewUsersCmd::SETTINGS->value;
        $this->prepareDependencies();
        // Fake that the chat was previously selected and it's id has been saved in cache
        (new PrivateChatCommandCore());


        $canSendMessages = $this->chat->newUserRestrictions->can_send_messages;
        $canSendMedia = $this->chat->newUserRestrictions->can_send_media;
        $restrictNewUsers = $this->chat->newUserRestrictions->restrict_new_users;


        $canSendMessages = $canSendMessages === 1 ? ResNewUsersCmd::DISABLE_SEND_MESSAGES->value : ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $canSendMedia = $canSendMedia === 1 ? ResNewUsersCmd::DISABLE_SEND_MEDIA->value : ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $restrictNewUsers = $restrictNewUsers === 1 ? ResNewUsersCmd::DISABLE_ALL->value : ResNewUsersCmd::ENABLE_ALL->value;

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString($canSendMessages, $sendMessageLog);
        $this->assertStringContainsString($canSendMedia, $sendMessageLog);
        $this->assertStringContainsString($restrictNewUsers, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SELECT_TIME->value, $sendMessageLog);
        $this->assertStringContainsString(MainMenuCmd::BACK->value, $sendMessageLog);
    }

    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SELECT_TIME->value;
        $this->prepareDependencies();

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_DAY->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_TWO_HOURS->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_WEEK->value, $sendMessageLog);
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_MONTH->value, $sendMessageLog);
        $this->clearTestLogFile();
    }


    public function testUpdateNewUsersRestrictionsTimeChangesValuesInDatabase()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SET_TIME_MONTH->value;
        $this->prepareDependencies();

        $this->setAllRestrictionsToFalse($this->chat);
        $this->assertEquals(0, $this->chat->newUserRestrictions->restrict_new_users);


        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(ResTime::MONTH->value, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_MONTH->replyMessage(), $sendMessageLog);
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_ALL->value;
        $this->prepareDependencies();
        // $previousCanSendMessagesStatus = $this->chat->newUserRestrictions->can_send_messages;
        // $previousCanSendMediaStatus = $this->chat->newUserRestrictions->can_send_media;
        $lastRestrictionTime = $this->chat->newUserRestrictions->restriction_time;

        //Setting only 'restrict_new_users' to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_messages);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertEquals($lastRestrictionTime, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_ALL->replyMessage(), $sendMessageLog);
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_ALL->value;
        $this->prepareDependencies();
        //Setting everything to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_ALL->replyMessage(), $sendMessageLog);
    }



    public function testEnableNewUsersCanSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $this->prepareDependencies();

        //Disable sending media for users before test
        $this->chat->newUserRestrictions->update([
            "can_send_media" => 0
        ]);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->can_send_media);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
    }

    public function testDisableNewUsersCanSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_SEND_MEDIA->value;
        $this->prepareDependencies();

        $this->chat->newUserRestrictions->update([
            "restrict_new_users" => 0, // Disable restrictions to make sure that it'll be enabled too
            "can_send_media" => 1 // Enable sending media
        ]);

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
    }


    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);
        $this->fakeChatSelected($this->admin->admin_id, $this->chat->chat_id);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }


    public function testNewUsersEnableSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $this->prepareDependencies();

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
    }

    public function testNewUsersDisableSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_SEND_MEDIA->value;
        $this->prepareDependencies();

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
    }

    public function testNewUsersEnableSendMessages()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $this->prepareDependencies();

        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_SEND_MESSAGES->replyMessage(), $sendMessageLog);
    }

    public function testNewUsersDisableSendMessages()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_SEND_MESSAGES->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);
        // Fake that chat was previously selected and its ID has been saved in cache
        (new PrivateChatCommandCore());
        $sendMessageLog = $this->getTestLogFile();
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_SEND_MESSAGES->replyMessage(), $sendMessageLog);
    }

}
