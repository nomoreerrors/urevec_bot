<?php

namespace Tests\Feature;

use App\Enums\ResNewUsersCmd;
use App\Models\TelegramRequestModelBuilder;
use App\Enums\ResTime;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Admin;
use App\Services\TelegramBotService;
use App\Services\PrivateChatCommandService;
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

    private $admin;
    private $data;
    private $model;
    private $botService;
    private $chat;
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        //Prepare one admin in database that attached to a few chats
        (new SimpleSeeder())->run(1, 5);
        $this->admin = Admin::first();
        $this->data = $this->getTextMessageModelData();
        // Assign fake admin id to correctly set $admin property in PrivateChatCommandService
        $this->data["message"]["from"]["id"] = $this->admin->admin_id;
        $this->data["message"]["chat"]["id"] = $this->admin->admin_id;
        //Prepare fake admins ids so that ModelBuilder can get them instead of calling api
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
    }

    public function testSelectSetRestrictNewUsersTimeReplyWithButtons()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::SELECT_TIME->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);
        //Fake that chat was previously selected and it's id has been saved in cache
        Cache::put("last_selected_chat_" . $this->model->getChatId(), $this->chat->chat_id);

        (new PrivateChatCommandService());
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
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 before test
        $this->setAllRestrictionsToFalse($this->chat);
        $this->assertEquals(0, $this->chat->newUserRestrictions->restrict_new_users);


        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(ResTime::MONTH->value, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::SET_TIME_MONTH->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testEnableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_ALL->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);
        // $previousCanSendMessagesStatus = $this->chat->newUserRestrictions->can_send_messages;
        // $previousCanSendMediaStatus = $this->chat->newUserRestrictions->can_send_media;
        $lastRestrictionTime = $this->chat->newUserRestrictions->restriction_time;

        //Setting only 'restrict_new_users' to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_messages);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        $this->assertEquals($lastRestrictionTime, $this->chat->newUserRestrictions()->first()->restriction_time);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_ALL->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }

    /**
     * Test toggleAllRestricitons method
     * @return void
     */
    public function testDisableNewUsersAllRestrictions()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_ALL->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Setting everything to 0 before test
        $this->setAllRestrictionsDisabled($this->chat);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_ALL->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }



    public function testEnableNewUsersCanSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        //Disable sending media for users before test
        $this->chat->newUserRestrictions->update([
            "can_send_media" => 0
        ]);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->can_send_media);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::ENABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }

    public function testDisableNewUsersCanSendMedia()
    {
        $this->data["message"]["text"] = ResNewUsersCmd::DISABLE_SEND_MEDIA->value;
        $this->prepareDependencies();
        $this->botService->setChat($this->chat->chat_id);

        $this->chat->newUserRestrictions->update([
            "restrict_new_users" => 0, // Disable restrictions to make sure that it'll be enabled too
            "can_send_media" => 1 // Enable sending media
        ]);

        (new PrivateChatCommandService());
        $sendMessageLog = $this->getTestLogFile();

        $this->assertEquals(1, $this->chat->newUserRestrictions()->first()->restrict_new_users);
        $this->assertEquals(0, $this->chat->newUserRestrictions()->first()->can_send_media);
        // Assert that the succeed reply message was sent
        $this->assertStringContainsString(ResNewUsersCmd::DISABLE_SEND_MEDIA->replyMessage(), $sendMessageLog);
        $this->clearTestLogFile();
    }


    private function prepareDependencies()
    {
        $this->chat = Chat::first();
        $this->model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->model);

        app()->instance("requestModel", $this->model);
        app()->instance("botService", $this->botService);
    }
}

