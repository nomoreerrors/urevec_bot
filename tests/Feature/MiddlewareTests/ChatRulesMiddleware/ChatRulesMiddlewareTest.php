<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\BaseTelegramRequestModel;
use App\Services\CONSTANTS;
use Tests\TestCase;

class ChatRulesMiddlewareTest extends TestCase
{
    public function test_if_message_contains_link_ban_user(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "https://google.com";
        $textModel = (new BaseTelegramRequestModel($data))->getModel();

        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);

        unset($data["message"]["text"]);
        $data["message"]["entities"]["url"] = "google.com";
        $this->assertTrue($textModel->getHasLink());
        $textModel = (new BaseTelegramRequestModel($data))->getModel();

        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_message_contains_upper_case_characters_link_ban_user(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "some text here and hTTps://gOOgle.com";
        $textModel = (new BaseTelegramRequestModel($data))->getModel();

        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }


    public function test_if_media_message_model_contains_link_ban_user(): void
    {
        $data = $this->getMultiMediaModel()->getData();
        $multiMediaModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertFalse($multiMediaModel->getHasLink());


        $data["message"]["caption"] = "some text and https://google.com";
        $multiMediaModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertTrue($multiMediaModel->getHasLink());


        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);


        $data["message"]["caption"] = "text without link";
        $data["message"]["caption_entities"]["type"] = "url";
        $multiMediaModel = (new BaseTelegramRequestModel($data))->getModel();
        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        $data = $this->getForwardMessageModel()->getData();
        $response = $this->post("api/webhook", $data);
        $response->isOk();
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
    }

    public function test_new_join_or_invited_user_restricted_automatically(): void
    {
        $data = $this->getInvitedUserUpdateModel()->getData();
        $response = $this->postJson("api/webhook", $data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);

        $data = $this->getNewMemberJoinUpdateModel()->getData();
        $response = $this->postJson("api/webhook", $data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);
    }
}
