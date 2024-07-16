<?php

namespace Tests\Feature;

use App\Models\StatusUpdateModel;
use App\Services\ChatRulesService;
use Tests\TestCase;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\BaseTelegramRequestModel;
use App\Models\InvitedUserUpdateModel;
use App\Services\CONSTANTS;

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

    public function test_message_has_link_but_not_able_to_delete_administrator_message()
    {
        $data = $this->getMessageModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $messageModel = (new BaseTelegramRequestModel($data))->getModel();

        $ruleService = new ChatRulesService($messageModel);
        $this->assertFalse($ruleService->ifMessageHasLinkBlockUser());
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

    /**
     * Test if a forwarded message from another group bans the user.
     *
     * This function sends a POST request to the "api/webhook" endpoint with the data obtained from the forwarded message model.
     * It asserts that the response is successful and contains the "MEMBER_BLOCKED" constant.
     *
     * Then, it modifies the "id" field of the "from" object in the data array to the admin ID.
     * It creates a new instance of the `BaseTelegramRequestModel` class with the modified data and calls the `create` method on it.
     * It creates a new instance of the `TelegramruleService` class with the created forward message model.
     * Finally, it asserts that the `blockUserIfMessageIsForward` method of the `TelegramruleService` instance returns `false`.
     *
     * @return void
     */
    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        $data = $this->getForwardMessageModel()->getData();
        $response = $this->post("api/webhook", $data);
        $response->isOk();
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);

        $data["message"]["from"]["id"] = $this->getAdminId();
        $forwardMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($forwardMessageModel);
        $this->assertFalse($service->blockUserIfMessageIsForward());
    }

    public function test_new_user_restricted_automatically(): void
    {
        $data = $this->getInvitedUserUpdateModel()->getData();
        $response = $this->postJson("api/webhook", $data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);

        $data["chat_member"]["from"]["id"] = $this->getAdminId();
        $newMemberJoinModel = new NewMemberJoinUpdateModel($data);
        // dd($newMemberJoinModel->getChatId());

        $response = $this->postJson("api/webhook", $data);

        $response->assertOk();
        $response->assertContent(CONSTANTS::NEW_MEMBER_RESTRICTED);
    }

    public function testInvitedUserRestrictedAutomatically(): void
    {
        $invitedUserUpdateData = $this->getInvitedUserUpdateModel()->getData();
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