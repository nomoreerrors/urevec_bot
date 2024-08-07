<?php

namespace Tests\Feature;

use App\Models\StatusUpdateModel;
use App\Services\ChatRulesService;
use App\Services\TelegramBotService;
use App\Models\Admin;
use App\Models\Chat;
use Database\Seeders\SimpleSeeder;
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

    private Chat $chat;

    private Admin $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSendMessageSucceedResponse();
        $this->fakeDeleteMessageSucceedResponse();
        $this->fakeRestrictMemberSucceedResponse();
        (new SimpleSeeder())->run(1, 5);
        $this->chat = Chat::first();
        $this->admin = $this->chat->admins->first();
        $this->data = $this->getMessageModelData();
        $this->fakeResponseWithAdminsIds($this->admin->admin_id, 66666);
    }

    /**
     * Tescase where is ifMessageHasLinkBlockUser() returns false if user is administrator
     * @method ifMessageHasLinkBlockUser
     * @return void
     */
    public function test_message_has_link_but_user_is_administrator_returns_false()
    {
        $this->data["message"]["from"]["id"] = $this->admin->admin_id;
        // Set prepared fake admin id from DB to model admins array instead of calling api 
        $requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->assertFalse((new ChatRulesService($requestModel))->ifMessageHasLinkBlockUser());
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
    public function test_if_forward_message_is_forwarded_by_admin_block_function_returns_false(): void
    {
        $this->data = $this->getMessageModelData();
        $this->data["message"]["from"]["id"] = $this->admin->admin_id;
        $this->data["message"]["forward_origin"] = [];

        $forwardMessageModel = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($forwardMessageModel);
        $this->assertFalse($service->blockUserIfMessageIsForward());
    }


    public function testNewUserJoinCheckRestrictionTimeInDatabaseAndBlockIfEnabled()
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $chatId = $this->chat->chat_id;
        $this->data["chat_member"]["chat"]["id"] = $chatId;

        $requestModel = (new TelegramRequestModelBuilder($this->data))->create();

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => CONSTANTS::RESTIME_WEEK,
            'can_send_messages' => 0,
            'can_send_media' => 0
        ]);

        $this->post('api/webhook', $this->data);

        $sendMessageLog = $this->getTestLogFile();

        $message = CONSTANTS::MEMBER_BLOCKED . " " . $requestModel->getFromId() .
            " " . "BLOCK TIME: " . "ONE WEEK";
        $this->assertStringContainsString($message, $sendMessageLog);
        $this->clearTestLogFile();
    }
}