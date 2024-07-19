<?php

namespace Tests\Feature;

use App\Models\StatusUpdateModel;
use App\Services\ChatRulesService;
use Tests\TestCase;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\BaseTelegramRequestModel;
use App\Models\InvitedUserUpdateModel;
use App\Services\CONSTANTS;

class ChatRulesServiceTest extends TestCase
{
    /**
     * Tescase where is ifMessageHasLinkBlockUser() returns false if user is administrator
     * @return void
     */
    public function test_message_has_link_but_user_is_administrator_returns_false()
    {
        $data = $this->getMessageModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $messageModel = (new BaseTelegramRequestModel($data))->getModel();

        $ruleService = new ChatRulesService($messageModel);
        $this->assertFalse($ruleService->ifMessageHasLinkBlockUser());
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

    /**
     * ifMessageContainsBlackListWordsBanUser method test using TextmessageModel
     * @return void
     */
    public function testifTextMessageModelContainsBlackListWordsBanUser(): void
    {
        //Testcase where text not contains any blacklisted word returns false
        $model = $this->getTextMessageModel();
        $data = $model->getData();
        $service = new ChatRulesService($model);
        $this->assertFalse($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains blacklisted word from badWords.json returns true
        $data["message"]["text"] = "администратор";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains blacklisted phrases from badPhrases.json returns true
        $data["message"]["text"] = "сдается в аренду";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains Chinese or Arabic etc. letters  returns true
        $data["message"]["text"] = "Arabic: ب تاء , Chinese: 我你 , Japanese: すせ";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());
    }

    /**
     * ifMessageContainsBlackListWordsBanUser method test using MultiMediaModel
     * @return void
     */
    public function testifMediaModelCaptionContainsBlackListWordsBanUser(): void
    {
        // Testcase where media model does not contain any blacklisted word returns false
        $model = $this->getMultimediaModel();
        $data = $model->getData();
        $service = new ChatRulesService($model);
        $this->assertFalse($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains blacklisted word from badWords.json returns true
        $data["message"]["caption"] = "администратор";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains blacklisted phrases from badPhrases.json returns true
        $data["message"]["caption"] = "Продаю свойский чеснок,сорт Грибоаский,можно на еду,на хранение и на посадку.Цена за 1 кг 300 руб, от трех кг по 250р.Все вопросы в личку. ";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains Chinese or Arabic etc. letters returns true
        $data["message"]["caption"] = "Arabic: ب تاء , Chinese: 我你 , Japanese: すせ";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());
    }

    /**
     * Testcase where the blockUserIfMessageIsForward function returns false if the message is forwarded by an administrator.
     * @return void
     */
    public function test_if_forward_message_is_forward_by_admin_block_function_returns_false(): void
    {
        $data = $this->getForwardMessageModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $forwardMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($forwardMessageModel);
        $this->assertFalse($service->blockUserIfMessageIsForward());
    }
}