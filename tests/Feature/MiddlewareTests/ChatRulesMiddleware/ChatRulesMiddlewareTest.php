<?php

namespace Tests\Feature\Middleware;

use App\Models\InvitedUserUpdateModel;
use App\Models\StatusUpdateModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class ChatRulesMiddlewareTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getTextMessageModelData();
    }
    public function test_if_message_contains_link_ban_user(): void
    {
        $this->data["message"]["text"] = "https://google.com";
        $textModel = (new BaseTelegramRequestModel($this->data))->getModel();

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->getMessageModelData()
            ], 200)
        ]);

        //Asserting that if text contains link member blocked
        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);

        unset($this->data["message"]["text"]);

        //Asserting that if entities has url key member blocked
        $this->data["message"]["entities"]["url"] = "google.com";
        $this->assertTrue($textModel->getHasLink());
        $textModel = (new BaseTelegramRequestModel($this->data))->getModel();

        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }


    public function test_if_message_contains_upper_case_characters_link_ban_user(): void
    {
        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->getMessageModelData()
            ], 200)
        ]);

        $this->data["message"]["text"] = "some text here and hTTps://gOOgle.com";
        $textModel = (new BaseTelegramRequestModel($this->data))->getModel();

        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }


    public function test_if_media_message_model_contains_link_ban_user(): void
    {
        $this->data = $this->getMultiMediaModelData();

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->data
            ], 200)
        ]);

        $multiMediaModel = (new BaseTelegramRequestModel($this->data))->getModel();
        //Asserting that normal MultimediaModel doesn't contain link
        $this->assertFalse($multiMediaModel->getHasLink());

        //Asserting that link is found if caption contains link
        $this->data["message"]["caption"] = "some text and https://google.com";
        $multiMediaModel = (new BaseTelegramRequestModel($this->data))->getModel();
        $this->assertTrue($multiMediaModel->getHasLink());

        //Asserting that  user is blocked
        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);

        //Asserting that  user is blocked if caption not contains link but the link is in caption_entities
        $this->data["message"]["caption"] = "text without link";
        $this->data["message"]["caption_entities"]["type"] = "url";
        $multiMediaModel = (new BaseTelegramRequestModel($this->data))->getModel();
        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        $this->data = $this->getForwardMessageModelData();

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->data
            ], 200)
        ]);

        $response = $this->post("api/webhook", $this->data);
        $response->isOk();
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_new_join_or_invited_user_restricted_automatically(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->data
            ], 200)
        ]);

        $response = $this->postJson("api/webhook", $this->data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);

        $this->data = $this->getNewMemberJoinUpdateModelData();
        $response = $this->postJson("api/webhook", $this->data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);
    }


    public function testInvitedUserRestrictedAutomatically(): void
    {
        $invitedUserUpdateData = $this->getInvitedUserUpdateModelData();
        $response = $this->postJson("api/webhook", $invitedUserUpdateData);

        $response->assertOk();
        $this->assertStringContainsString(
            CONSTANTS::NEW_MEMBER_RESTRICTED,
            $response->getContent()
        );

        $invitedUserUpdateData["chat_member"]["from"]["id"] = $this->getAdminId();
        $invitedUserUpdateData["chat_member"]["old_chat_member"]["status"] = "restricted";
        $statusUpdateModel = (new BaseTelegramRequestModel($invitedUserUpdateData))->getModel();

        $this->assertInstanceOf(StatusUpdateModel::class, $statusUpdateModel);
        $this->assertNotInstanceOf(InvitedUserUpdateModel::class, $statusUpdateModel);

        $telegramBotService = new ChatRulesService($statusUpdateModel);
        $this->assertFalse($telegramBotService->blockNewVisitor());
    }
}
