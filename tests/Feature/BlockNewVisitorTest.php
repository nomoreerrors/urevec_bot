<?php

namespace Tests\Feature;

use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseTelegramRequestModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\StatusUpdateModel;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;
use Error;
use ErrorException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Log;

use function Laravel\Prompts\error;

class BlockNewVisitorTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function test_block_new_user_returned_true(): void
    {
        $newMemberUpdate = $this->getNewMemberUpdateModel();

        $service = new TelegramBotService($newMemberUpdate);
        $this->assertTrue($service->blockNewVisitor());

        $result = (new BaseTelegramRequestModel($newMemberUpdate->getData()))->create();
        $this->assertInstanceOf(NewMemberJoinUpdateModel::class, $result);
    }



    public function test_if_not_a_new_member_and_not_invited_user_instance_block_failed_and_returns_false(): void
    {
        $message = $this->getMessageModel();
        $service = new TelegramBotService($message);

        $this->assertFalse($service->blockNewVisitor());
    }


    /**
     * Make sure is new member and not left user
     *
     * @return void
     */
    public function test_new_chat_member_status_not_equals_member_returns_false(): void
    {
        $newMemberUpdateModel = $this->getNewMemberUpdateModel();
        $data = $newMemberUpdateModel->getData();
        $data['chat_member']['new_chat_member']['status'] = 'left';
        $statusUpdateModel = (new BaseTelegramRequestModel($data))->create();

        $service = new TelegramBotService($statusUpdateModel);
        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($statusUpdateModel instanceof NewMemberJoinUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $statusUpdateModel);
    }



    //TODO
    // public function test_a_few_new_users_invited_blocked_everyone(): void
    // {
    //     $message = $this->getInvitedUserUpdateModel();
    //     $service = new TelegramBotService($message);
    //     $this->assertTrue($service->blockNewVisitor());
    // }



    public function testBlockingNewInvitedUserReturnsTrue(): void
    {
        $invitedUserUpdateModel = $this->getInvitedUserUpdateModel();
        $service = new TelegramBotService($invitedUserUpdateModel);
        $this->assertTrue($service->blockNewVisitor());
    }




    public function testIfUserUnrestrictedByAdminModelTypeIsNotInvitedUserModelAndReturnsFalse(): void
    {
        $invitedUserUpdateModel = $this->getInvitedUserUpdateModel();
        $data = $invitedUserUpdateModel->getData();

        $data['chat_member']['from']['id'] = $this->getAdminId();
        $data['chat_member']['old_chat_member']['status'] = 'restricted';
        $data['chat_member']['new_chat_member']['status'] = 'member';

        $message = (new BaseTelegramRequestModel($data))->create();
        $service = new TelegramBotService($message);

        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertInstanceOf(StatusUpdateModel::class, $message);
    }


    public function test_user_restricted_by_admin_generated_model_is_status_update_model_and_block_new_user_function_returns_false(): void
    {
        $data = ($this->getInvitedUserUpdateModel())->getData();

        $data["chat_member"]["from"]["id"] = $this->getAdminId();
        $data["chat_member"]["old_chat_member"]["status"] = "member";
        $data["chat_member"]["new_chat_member"]["status"] = "restricted";

        $message = (new BaseTelegramRequestModel($data))->create();
        $service = new TelegramBotService($message);

        $this->assertFalse($service->blockNewVisitor());


        $data["chat_member"]["new_chat_member"]["status"] = "kicked";
        $message = (new BaseTelegramRequestModel($data))->create();
        $service = new TelegramBotService($message);
        $this->assertFalse($service->blockNewVisitor());
        $this->assertFalse($message instanceof InvitedUserUpdateModel);
        $this->assertFalse($message instanceof NewMemberJoinUpdateModel);
        $this->assertTrue($message instanceof StatusUpdateModel);
    }
}
