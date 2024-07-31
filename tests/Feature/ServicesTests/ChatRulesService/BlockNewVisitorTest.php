<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Models\StatusUpdates\InvitedUserUpdateModel;
use App\Models\StatusUpdates\NewMemberJoinUpdateModel;
use App\Models\StatusUpdates\StatusUpdateModel;
use App\Services\ChatRulesService;
use Tests\TestCase;

class BlockNewVisitorTest extends TestCase
{
    public function test_if_is_a_new_member_join_update_model_method_returns_true(): void
    {
        $data = $this->getNewMemberJoinUpdateModelData();
        $model = (new TelegramRequestModelBuilder($data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->blockNewVisitor());

        $result = (new TelegramRequestModelBuilder($data))->create();
        $this->assertInstanceOf(NewMemberJoinUpdateModel::class, $result);
        sleep(5);
    }

    /**
     * Test the BlockNewVisitor method when the model is not a NewMemberJoinUpdateModel or InvitedUserUpdateModel.
     *
     * @return void
     */
    public function testBlockingNewVisitorWithInvalidModelReturnsFalse(): void
    {
        $messageModelData = $this->getMessageModelData();
        $messageModel = (new TelegramRequestModelBuilder($messageModelData))->create();
        $chatRulesService = new ChatRulesService($messageModel);

        $this->assertFalse($chatRulesService->blockNewVisitor());
    }

    /**
     *  Make sure that if request new member status "left" the model isn't a NewMemberJoinUpdateModel
     *  and BlockNewUser method returns false
     * @return void
     */
    public function testBlockingNewMemberWithNonMemberStatusReturnsFalse(): void
    {
        $data = $this->getNewMemberJoinUpdateModelData();
        $data['chat_member']['new_chat_member']['status'] = 'left';

        $requestModel = new TelegramRequestModelBuilder($data);
        $statusUpdateModel = $requestModel->create();

        $this->assertInstanceOf(StatusUpdateModel::class, $statusUpdateModel);

        $chatRulesService = new ChatRulesService($statusUpdateModel);
        $this->assertFalse($chatRulesService->blockNewVisitor());
        $this->assertFalse($statusUpdateModel instanceof NewMemberJoinUpdateModel);
    }

    //TODO
    // public function test_a_few_new_users_invited_blocked_everyone(): void
    // {
    //     $message = $this->getInvitedUserUpdateModel();
    //     $service = new ChatRulesService($message);
    //     $this->assertTrue($service->blockNewVisitor());
    // }


    public function testBlockingNewInvitedUserReturnsTrue(): void
    {
        $data = $this->getInvitedUserUpdateModelData();
        $model = (new TelegramRequestModelBuilder($data))->create();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->blockNewVisitor());
        sleep(5);
    }

    /**
     * Testcase where administrator of chat unrestricts user and returned model type is not InvitedUserModel
     * but is StatusUpdateModel and BlockNewUser method returns false and doesn't post request to telegram
     * @return void
     */
    public function testIfUserUnrestrictedByAdminModelTypeIsNotInvitedUserModelAndReturnsFalse(): void
    {
        $data = $this->getInvitedUserUpdateModelData();
        $model = (new TelegramRequestModelBuilder($data))->create();

        $data['chat_member']['from']['id'] = $this->getAdminId();
        $data['chat_member']['old_chat_member']['status'] = 'restricted';
        $data['chat_member']['new_chat_member']['status'] = 'member';

        $message = (new TelegramRequestModelBuilder($data))->create();
        $service = new ChatRulesService($message);

        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $message);
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
        $messageData = $this->getInvitedUserUpdateModelData();

        $messageData["chat_member"]["from"]["id"] = $this->getAdminId();
        $messageData["chat_member"]["old_chat_member"]["status"] = "member";

        $this->assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel($messageData, "restricted");
        $this->assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel($messageData, "kicked");
    }

    private function assertAdminRestrictsOrKicksUserAndReturnsStatusUpdateModel(array $messageData, string $status): void
    {
        $messageData["chat_member"]["new_chat_member"]["status"] = $status;

        $message = (new TelegramRequestModelBuilder($messageData))->create();
        $service = new ChatRulesService($message);
        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertFalse($message instanceof NewMemberJoinUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $message);
    }
}
