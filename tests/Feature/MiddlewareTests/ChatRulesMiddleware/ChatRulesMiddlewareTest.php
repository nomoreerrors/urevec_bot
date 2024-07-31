<?php

namespace Tests\Feature\Middleware;

use App\Models\StatusUpdates\StatusUpdateModel;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\ChatRulesService;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Support\Facades\Http;
use App\Services\CONSTANTS;
use Tests\TestCase;

class ChatRulesMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    protected array $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getTextMessageModelData();
        // Adding fake chat to database to avoid http calls to api to get admins ids and creating a new chat in database
        $this->addFakeChatToDatabase();
    }

    /**
     * User message contains link and should be blocked  
     * Assuming that chat already exists in database and already mocked constructor
     * @return void
     */
    public function test_if_request_text_contains_link_ban_user(): void
    {
        $this->data["message"]["text"] = "https://google.com";
        $textModel = (new TelegramRequestModelBuilder($this->data))->create();
        // Fake response for DeleteMessage function
        $this->fakeSucceedResponse();
        //Asserting that if text contains link member blocked
        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
        // Delete text key to set link to another key
        unset($this->data["message"]["text"]);
        //Asserting that if entities has url key member blocked
        $this->data["message"]["entities"]["url"] = "google.com";
        $this->assertTrue($textModel->getHasLink());
        $textModel = (new TelegramRequestModelBuilder($this->data))->create();

        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
        sleep(5);
    }


    public function test_if_request_text_contains_upper_case_characters_link_ban_user(): void
    {
        // Fake response for DeleteMessage function
        $this->fakeSucceedResponse();
        $this->data["message"]["text"] = "some text here and hTTps://gOOgle.com";
        $textModel = (new TelegramRequestModelBuilder($this->data))->create();

        $this->assertTrue($textModel->getHasLink());
        $response = $this->post("api/webhook", $textModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
        sleep(5);
    }


    public function test_if_media_message_model_contains_link_ban_user(): void
    {
        $this->fakeSucceedResponse();
        $this->data = $this->getMultiMediaModelData();
        $multiMediaModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->data["message"]["caption"] = "some text and https://google.com";
        $multiMediaModel = (new TelegramRequestModelBuilder($this->data))->create();
        //Asserting that haslink is set to true  caption contains link
        $this->assertTrue($multiMediaModel->getHasLink());

        //Asserting that  user is blocked
        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);

        //Asserting that  user is blocked if caption not contains link but the link is in caption_entities
        $this->data["message"]["caption"] = "text without link";
        $this->data["message"]["caption_entities"]["type"] = "url";
        $multiMediaModel = (new TelegramRequestModelBuilder($this->data))->create();
        $response = $this->post("api/webhook", $multiMediaModel->getData());
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
        sleep(5);
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        // Fake response for DeleteMessage function
        $this->fakeSucceedResponse();
        $this->data = $this->getMessageModelData();
        $this->data["message"]["forward_origin"] = [];

        $response = $this->post("api/webhook", $this->data);
        $response->isOk();
        $response->assertSee(CONSTANTS::MEMBER_BLOCKED);
        sleep(5);
    }

    public function test_new_join_or_invited_user_restricted_automatically(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();

        $response = $this->postJson("api/webhook", $this->data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);

        $this->data = $this->getNewMemberJoinUpdateModelData();
        $response = $this->postJson("api/webhook", $this->data);
        $response->assertOk();
        $response->assertSee(CONSTANTS::NEW_MEMBER_RESTRICTED);
        sleep(5);
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

        sleep(5);

        $invitedUserUpdateData["chat_member"]["old_chat_member"]["status"] = "restricted";
        $statusUpdateModel = (new TelegramRequestModelBuilder($invitedUserUpdateData))->create();

        // Asserting that if new chat member or old chat member status not equals left and member the model isn't a InvitedUserUpdateModel
        $this->assertInstanceOf(StatusUpdateModel::class, $statusUpdateModel);
        $this->assertNotInstanceOf(InvitedUserUpdateModel::class, $statusUpdateModel);

        $telegramBotService = new ChatRulesService($statusUpdateModel);
        $this->assertFalse($telegramBotService->blockNewVisitor());
    }
}
