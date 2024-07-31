<?php

namespace Tests\Feature;

use App\Models\StatusUpdateModel;
use App\Services\ChatRulesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\TelegramRequestModelBuilder;
use App\Services\CONSTANTS;

class ChatRulesServiceTest extends TestCase
{
    use RefreshDatabase;
    private array $data;
    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
    }

    /**
     * Tescase where is ifMessageHasLinkBlockUser() returns false if user is administrator
     * @method ifMessageHasLinkBlockUser
     * @return void
     */
    public function test_message_has_link_but_user_is_administrator_returns_false()
    {
        $this->data["message"]["from"]["id"] = $this->getAdminId();
        $messageModel = (new TelegramRequestModelBuilder($this->data))->create();
        //Prepare admin id in cache
        Cache::put(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $messageModel->getChatId(), [$this->getAdminId()]);

        $ruleService = new ChatRulesService($messageModel);
        $this->assertFalse($ruleService->ifMessageHasLinkBlockUser());
    }


    /**
     * ifMessageContainsBlackListWordsBanUser method test using TextmessageModel
     * @method ifMessageContainsBlackListWordsBanUser
     * @return void
     */
    public function testifTextMessageModelContainsBlackListWordsBanUser(): void
    {
        //Testcase where text not contains any blacklisted word returns false
        $this->data = $this->getTextMessageModelData();
        $chatId = $this->data["message"]["chat"]["id"];

        //Prepare admin id in cache so it can be used to check if user is admin
        Cache::put(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $chatId, [$this->getAdminId()]);

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->getMessageModelData()
            ], 200)
        ]);


        $model = (new TelegramRequestModelBuilder($this->data))->create();

        $service = new ChatRulesService($model);
        $this->assertFalse($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains blacklisted word from badWords.json returns true
        $this->data["message"]["text"] = "администратор";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains blacklisted phrases from badPhrases.json returns true
        $this->data["message"]["text"] = "сдается в аренду";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        //Testcase where text contains Chinese or Arabic etc. letters  returns true
        $this->data["message"]["text"] = "Arabic: ب تاء , Chinese: 我你 , Japanese: すせ";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());
    }

    /**
     * @method ifMessageContainsBlackListWordsBanUser
     * @return void
     */
    public function testifMediaModelCaptionContainsBlackListWordsBanUser(): void
    {
        $this->data = $this->getMultiMediaModelData();
        $chatId = $this->data["message"]["chat"]["id"];

        //Prepare admin id in cache so it can be used to check if user is admin
        Cache::put(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $chatId, [$this->getAdminId()]);

        Http::fake([
            '*' => Http::response([
                'ok' => true,
                'result' => $this->data
            ], 200)
        ]);

        // Testcase where media model does not contain any blacklisted word returns false
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertFalse($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains blacklisted word from badWords.json returns true
        $this->data["message"]["caption"] = "администратор";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains blacklisted phrases from badPhrases.json returns true
        $this->data["message"]["caption"] = "Продаю свойский чеснок,сорт Грибоаский,можно на еду,на хранение и на посадку.Цена за 1 кг 300 руб, от трех кг по 250р.Все вопросы в личку. ";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());

        // Testcase where media model contains Chinese or Arabic etc. letters returns true
        $this->data["message"]["caption"] = "Arabic: ب تاء , Chinese: 我你 , Japanese: すせ";
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->ifMessageContainsBlackListWordsBanUser());
    }

    /**
     * Testcase where the blockUserIfMessageIsForward function returns false if the message is forwarded by an administrator.
     * @method blockUserIfMessageIsForward 
     * @return void
     */
    public function test_if_forward_message_is_forward_by_admin_block_function_returns_false(): void
    {
        $this->data = $this->getMessageModelData();
        $this->data["message"]["from"]["id"] = $this->getAdminId();
        $this->data["message"]["forward_origin"] = [];
        $chatId = $this->data["message"]["chat"]["id"];
        //Prepare admin id in cache so it can be used to check if user is admin
        Cache::put(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $chatId, [$this->getAdminId()]);

        $forwardMessageModel = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($forwardMessageModel);
        $this->assertFalse($service->blockUserIfMessageIsForward());
    }
}