<?php

namespace Tests\Feature\Middleware\ChatRulesService;

use App\Models\BaseTelegramRequestModel;
use App\Models\MessageModel;
use Illuminate\Support\Facades\Http;
use App\Models\TextMessageModel;
use Illuminate\Support\Facades\Cache;
use App\Services\ChatRulesService;
use App\Services\CONSTANTS;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IfMessageHasLinkBlockUserTest extends TestCase
{
    /**
     * Test that ifMessageHasLinkBlockUser returns false if the user is an admin.
     */
    public function test_if_user_is_admin_returns_false()
    {
        $data = $this->getMessageModelData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $chatId = $data["message"]["chat"]["id"];
        $messageModel = (new BaseTelegramRequestModel($data))->getModel();
        //prepare admins id in cache
        Cache::put(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $chatId, [$this->getAdminId()]);


        $chatRulesService = new ChatRulesService($messageModel);
        $this->assertFalse($chatRulesService->ifMessageHasLinkBlockUser());
    }

    /**
     * Test that ifMessageHasLinkBlockUser returns false if the message is not a MessageModel or TextMessageModel.
     */
    public function test_if_message_is_not_message_model_instance_returns_false()
    {
        $data = $this->getNewMemberJoinUpdateModelData();
        $model = (new BaseTelegramRequestModel($data))->getModel();

        $chatRulesService = new ChatRulesService($model);
        $this->assertFalse($chatRulesService->ifMessageHasLinkBlockUser());
    }

    /**
     * Test that ifMessageHasLinkBlockUser calls banUser if the message has a link.
     */
    public function test_if_message_has_link_return_true()
    {
        $data = $this->getTextMessageModelData();
        $data['message']['text'] = "https://www.google.com";
        $model = (new BaseTelegramRequestModel($data))->getModel();

        Http::fake([
            '*' => Http::response($data, 200),
        ]);

        $chatRulesService = new ChatRulesService($model);

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }

    /**
     * Test that ifMessageHasLinkBlockUser calls banUser if the message has a text link.
     */
    public function test_if_message_has_text_link_returns_true()
    {
        $data = $this->getMessageModelData();
        $data["message"]["entities"]["type"] = "text_link";
        $model = (new BaseTelegramRequestModel($data))->getModel();

        Http::fake([
            '*' => Http::response($data, 200),
        ]);

        $chatRulesService = new ChatRulesService($model);
        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }

    /**
     * Same tests but with MultiMediaModel tests and caption key
     * 
     * 
     * 
     * 
     * 
     * Summary of test_if_multimediamodel_has_text_link_calls_ban_user_and_returns_true
     * @return void
     */
    public function test_if_multimediamodel_has_text_link_calls_ban_user_and_returns_true()
    {
        $data = $this->getMultiMediaModelData();
        $data["message"]["caption_entities"]["type"] = "text_link";
        $model = (new BaseTelegramRequestModel($data))->getModel();

        Http::fake([
            '*' => Http::response($data, 200),
        ]);

        $chatRulesService = new ChatRulesService($model);
        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }

    public function test_if_media_model_caption_contains_link_and_returns_true()
    {
        $data = $this->getMultiMediaModelData();
        $data['message']['caption'] = "https://www.google.com";
        $model = (new BaseTelegramRequestModel($data))->getModel();

        Http::fake([
            '*' => Http::response($data, 200),
        ]);

        $chatRulesService = new ChatRulesService($model);

        $this->assertTrue($chatRulesService->ifMessageHasLinkBlockUser());
    }
}
