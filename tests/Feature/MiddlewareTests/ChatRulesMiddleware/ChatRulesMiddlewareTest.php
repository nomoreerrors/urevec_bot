<?php

namespace Tests\Feature\Middleware;

use App\Enums\BanMessages;
use App\Enums\ResNewUsersCmd;
use App\Enums\ResTime;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
use Tests\TestCase;

class ChatRulesMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    protected array $data;

    protected $requestModel;

    protected Chat $chat;

    protected TelegramBotService $botService;

    protected ChatRulesService $rulesService;


    protected function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder())->run();
        $this->chat = Chat::first();
        $this->fakeDeleteMessageSucceedResponse();
        $this->fakeSendMessageSucceedResponse();
        $this->fakeRestrictMemberSucceedResponse();
    }

    /**
     * User message contains link and should be blocked  
     * Assuming that chat already exists in database and already mocked constructor
     * @return void
     */
    public function test_if_request_text_contains_link_ban_user(): void
    {
        $this->data = $this->getMessageModelData();
        $this->data["message"]["text"] = "https://google.com";
        $this->prepareDependencies("message");
        //Asserting that if text contains link member blocked
        $this->assertTrue($this->requestModel->getHasLink());
        $response = $this->post("api/webhook", $this->requestModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_request_entities_contains_link_ban_user(): void
    {
        $this->data = $this->getTextMessageModelData();
        $this->data["message"]["entities"][0]["url"] = "google.com";
        $this->prepareDependencies("message");
        $this->assertTrue($this->requestModel->getHasLink());

        $response = $this->post("api/webhook", $this->requestModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_request_text_contains_upper_case_characters_link_ban_user(): void
    {
        $this->data = $this->getMessageModelData();
        $this->data["message"]["text"] = "some text here and hTTps://gOOgle.com";
        $this->prepareDependencies("message");

        $this->assertTrue($this->requestModel->getHasLink());
        $response = $this->post("api/webhook", $this->requestModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_media_message_model_caption_contains_link_ban_user(): void
    {
        $this->data = $this->getMultiMediaModelData();
        $this->data["message"]["caption"] = "some text and https://google.com";
        $this->prepareDependencies("message");
        //Asserting that haslink is set to true  caption contains link
        $this->assertTrue($this->requestModel->getHasLink());
        //Asserting that  user is blocked
        $response = $this->post("api/webhook", $this->requestModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_media_message_model_caption_entities_contains_link_ban_user(): void
    {
        $this->data = $this->getMultiMediaModelData();
        $this->data["message"]["caption"] = "text without link";
        $this->data["message"]["caption_entities"][0]["type"] = "url";

        $this->prepareDependencies("message");

        $response = $this->post("api/webhook", $this->requestModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        $this->data = $this->getMessageModelData();
        $this->data["message"]["forward_origin"] = [];
        $this->prepareDependencies("message");

        $response = $this->post("api/webhook", $this->data)->assertOk();
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_invited_user_restricted_automatically(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $this->prepareDependencies("chat_member");
        $this->setAllRestrictionsEnabled($this->chat);
        $this->postJson("api/webhook", $this->data)->assertOk();

        $testLogFile = $this->getTestLogFile();
        $this->assertStringContainsString(BanMessages::INVITED_USER_BLOCKED->value, $testLogFile);
        $this->clearTestLogFile();
    }

    public function test_new_joined_user_restricted_automatically_and_the_restriction_time_is_getting_from_database(): void
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $this->setAllRestrictionsEnabled($this->chat);
        $restrictionTime = ResTime::from($this->chat->newUserRestrictions->restriction_time);
        $this->prepareDependencies("chat_member");

        $this->postJson("api/webhook", $this->data)->assertOk();
        $testLogFile = $this->getTestLogFile();

        $this->assertStringContainsString(BanMessages::NEW_MEMBER_RESTRICTED->value, $testLogFile);
        $this->assertStringContainsString($restrictionTime->getHumanRedable(), $testLogFile);
        $this->clearTestLogFile();
    }


    public function testRestrictionsDisabledNewJoinedUserNotBlocked()
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $chatId = $this->chat->chat_id;
        $userId = $this->data["chat_member"]["from"]["id"];
        $this->data["chat_member"]["chat"]["id"] = $chatId;

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 0,
        ]);

        $this->post('api/webhook', $this->data);

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringNotContainsString(BanMessages::NEW_MEMBER_RESTRICTED->withId($userId), $sendMessageLog);
    }


    public function testRestrictionsDisabledInvitedUserNotBlocked()
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $chatId = $this->chat->chat_id;
        $invitedUserId = $this->data["chat_member"]["new_chat_member"]["user"]["id"];
        $this->data["chat_member"]["chat"]["id"] = $chatId;
        (new TelegramRequestModelBuilder($this->data))->create();

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 0,
        ]);

        $this->post('api/webhook', $this->data);

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringNotContainsString(BanMessages::INVITED_USER_BLOCKED->withId($invitedUserId), $sendMessageLog);
    }


    /**
     * Types : "message", "chat_member"
     * @param string $type
     * @return void
     */
    public function prepareDependencies(string $type)
    {
        $this->data[$type]["chat"]["id"] = $this->chat->chat_id;
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->requestModel);
        $this->botService->setChat($this->chat->chat_id);
        app()->singleton("botService", fn() => $this->botService);
        $this->rulesService = new ChatRulesService($this->requestModel);
        $this->clearTestLogFile();
    }


    public function testUserInvitedCheckRestrictionTimeInDatabaseAndBlockIfEnabled()
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $chatId = $this->chat->chat_id;
        $invitedUserId = $this->data["chat_member"]["new_chat_member"]["user"]["id"];
        $this->data["chat_member"]["chat"]["id"] = $chatId;
        (new TelegramRequestModelBuilder($this->data))->create();

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => ResTime::DAY->value,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);

        $this->post('api/webhook', $this->data);

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString(BanMessages::INVITED_USER_BLOCKED->withId($invitedUserId), $sendMessageLog);
        $this->assertStringContainsString(ResTime::DAY->getHumanRedable(), $sendMessageLog);
    }


    public function testNewUserJoinCheckRestrictionTimeInDatabaseAndBlockIfEnabled()
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $chatId = $this->chat->chat_id;
        $userId = $this->data["chat_member"]["from"]["id"];
        $this->data["chat_member"]["chat"]["id"] = $chatId;


        (new TelegramRequestModelBuilder($this->data))->create();

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => ResTime::WEEK->value,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);

        $this->post('api/webhook', $this->data);

        $sendMessageLog = $this->getTestLogFile();

        $this->assertStringContainsString(BanMessages::NEW_MEMBER_RESTRICTED->withId($userId), $sendMessageLog);
        $this->assertStringContainsString(ResTime::WEEK->getHumanRedable(), $sendMessageLog);
    }
}
