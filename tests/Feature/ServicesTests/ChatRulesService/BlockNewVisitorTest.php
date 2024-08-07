<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Models\Chat;
use App\Exceptions\BaseTelegramBotException;
use App\Services\CONSTANTS;
use App\Services\TelegramBotService;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Services\ChatRulesService;
use Database\Seeders\SimpleSeeder;
use Tests\TestCase;

/**
 * Test BlockNewVisitorTest method of ChatRulesService
 */
class BlockNewVisitorTest extends TestCase
{
    protected array $data;
    protected ChatRulesService $rulesService;
    protected $requestModel;

    protected Chat $chat;

    protected TelegramBotService $botService;

    public function setUp(): void
    {
        parent::setUp();
        (new SimpleSeeder)->run();
        $this->chat = Chat::first();
        $this->fakeRestrictMemberSucceedResponse();
    }

    public function test_new_member_join_update_model_method_returns_true(): void
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $this->prepareDependencies();
        $this->assertTrue($this->rulesService->blockNewVisitor());
    }

    /**
     * Test the BlockNewVisitor method when the model is not a NewMemberJoinUpdateModel or InvitedUserUpdateModel.
     *
     * @return void
     */
    public function testBlockingNewVisitorWithInvalidModelReturnsFalse(): void
    {
        $this->data = $this->getMessageModelData();
        $this->prepareDependencies();
        $this->assertFalse($this->rulesService->blockNewVisitor());
    }

    /**
     *  Make sure that if request new member status "left" the model isn't a NewMemberJoinUpdateModel
     *  or invited update model
     *  and BlockNewUser method returns false
     * @return void
     */
    public function testBlockingNewMemberWithNonMemberStatusReturnsFalse(): void
    {
        $this->data = $this->getNewMemberJoinUpdateModelData();
        $this->prepareDependencies();
        $this->data['chat_member']['new_chat_member']['status'] = 'left';
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();

        $this->assertInstanceOf(StatusUpdateModel::class, $this->requestModel);
        $this->rulesService = new ChatRulesService($this->requestModel);
        $this->assertFalse($this->rulesService->blockNewVisitor());
        $this->assertFalse($this->requestModel instanceof NewMemberJoinUpdateModel);
    }


    public function testBlockingNewInvitedUserReturnsTrue(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $this->prepareDependencies();
        $this->assertTrue($this->rulesService->blockNewVisitor());
    }

    /**
     * Testcase where administrator of chat unrestricts user and returned model type is not InvitedUserModel
     * but is StatusUpdateModel and BlockNewUser method returns false and doesn't post request to telegram
     * @return void
     */
    public function testIfUserUnrestrictedByAdminModelTypeIsNotInvitedUserModelAndReturnsFalse(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $this->data['chat_member']['from']['id'] = $this->getAdminId();
        $this->data['chat_member']['old_chat_member']['status'] = 'restricted';
        $this->data['chat_member']['new_chat_member']['status'] = 'member';
        $this->prepareDependencies();

        $this->assertFalse($this->rulesService->blockNewVisitor());
        $this->assertFalse($this->requestModel instanceof InvitedUserUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $this->requestModel);
    }

    /**
     * Make sure that if request object's lnew member status "restricted" or "kicked" the model isn't a InvitedUserUpdateModel
     * This means that the user restricted by admin or kicked
     * it needs because of creating of  InvitedUserUpdateModel based on that [chat_member][user][id] and [chat_member][new_chat_member][id] are different
     * and in this case user id is admin's id and it's different to new_chat_member id
     * @return void
     */
    public function testIfAdminRestrictsOrKicksUserRequestModelIsStatusUpdateModel(): void
    {
        $this->data = $this->getInvitedUserUpdateModelData();
        $this->data["chat_member"]["from"]["id"] = $this->getAdminId();
        $this->data["chat_member"]["old_chat_member"]["status"] = "member";
        $this->prepareDependencies();

        $this->assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel($this->data, "restricted");
        $this->assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel($this->data, "kicked");
    }

    private function assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel(array $messageData, string $status): void
    {
        $this->data["chat_member"]["new_chat_member"]["status"] = $status;

        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $service = new ChatRulesService($this->requestModel);
        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($this->requestModel instanceof InvitedUserUpdateModel);
        $this->assertFalse($this->requestModel instanceof NewMemberJoinUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $this->requestModel);
    }

    /**
     * @property mixed $messageType : invited, joined
     */
    private function prepareDependencies()
    {
        $this->requestModel = (new TelegramRequestModelBuilder($this->data))->create();
        $this->botService = new TelegramBotService($this->requestModel);
        $this->botService->setChat($this->chat->chat_id);
        app()->singleton("botService", fn() => $this->botService);
        $this->rulesService = new ChatRulesService($this->requestModel);
    }

}
