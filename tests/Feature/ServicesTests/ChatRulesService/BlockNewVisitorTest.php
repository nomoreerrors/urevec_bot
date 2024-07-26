<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
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
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->blockNewVisitor());

        $result = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(NewMemberJoinUpdateModel::class, $result);
    }

    public function test_if_not_a_new_member_join_update_model_and_not_invited_user_model_block_failed_and_returns_false(): void
    {
        $data = $this->getMessageModelData();
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertFalse($service->blockNewVisitor());
    }

    /**
     * Test BlockNewVisitor method
     * @return void
     */
    public function test_if_new_chat_member_status_not_equals_member_returned_model_is_status_update_model_and_block_new_user_function_returns_false(): void
    {
        $data = $this->getNewMemberJoinUpdateModelData();
        $data['chat_member']['new_chat_member']['status'] = 'left';

        $statusUpdateModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(StatusUpdateModel::class, $statusUpdateModel);

        $service = new ChatRulesService($statusUpdateModel);
        $this->assertFalse($service->blockNewVisitor());
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
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($model);
        $this->assertTrue($service->blockNewVisitor());
    }

    /**
     * Testcase where administrator of chat unrestricts user and returned model type is not InvitedUserModel
     * but is StatusUpdateModel and BlockNewUser method returns false
     * @return void
     */
    public function testIfUserUnrestrictedByAdminModelTypeIsNotInvitedUserModelAndReturnsFalse(): void
    {
        $data = $this->getInvitedUserUpdateModelData();
        $model = (new BaseTelegramRequestModel($data))->getModel();

        $data['chat_member']['from']['id'] = $this->getAdminId();
        $data['chat_member']['old_chat_member']['status'] = 'restricted';
        $data['chat_member']['new_chat_member']['status'] = 'member';

        $message = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($message);

        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $message);
    }

    /**
     * Testcase where user restricted or kicked by administrator and returned model type is not an InvitedUserModel
     * but is StatusUpdateModel and BlockNewUser method returns false. Because generating InvitedUserModel is based on
     * the fact that "["from"]["user"]["id"] key and "["new_chat_member"]["user"]["id"] key are not equal". So we make sure
     * that if user is administrator will not lead to an error and  that creating model is pure StatusUpdateModel 
     * @return void
     */
    public function test_user_restricted_by_admin_returned_model_type_is_status_update_model_and_block_new_user_function_returns_false(): void
    {
        $data = ($this->getInvitedUserUpdateModelData());

        $data["chat_member"]["from"]["id"] = $this->getAdminId();
        $data["chat_member"]["old_chat_member"]["status"] = "member";
        $data["chat_member"]["new_chat_member"]["status"] = "restricted";

        $message = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($message);
        $this->assertFalse($service->blockNewVisitor());


        $data["chat_member"]["new_chat_member"]["status"] = "kicked";
        $message = (new BaseTelegramRequestModel($data))->getModel();
        $service = new ChatRulesService($message);
        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertFalse($message instanceof NewMemberJoinUpdateModel);
        $this->assertTrue($message instanceof StatusUpdateModel);
    }
}
