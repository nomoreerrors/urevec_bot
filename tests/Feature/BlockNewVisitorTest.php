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

    public function test_block_new_user_response_result_true(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();
      
                    $service = new TelegramBotService($message);

                    if ($message instanceof NewMemberJoinUpdateModel) {
                        
                        $result = $service->blockNewVisitor();
                        if ($result === true) {
                            $this->assertTrue($result);
                        }
                    }
            }
    }

    public function test_if_not_a_new_member_instance_block_failed_and_returns_false(): void
    {

        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);


            if (!($message instanceof NewMemberJoinUpdateModel) &&
                !($message instanceof InvitedUserUpdateModel)) {

                $this->assertFalse($service->blockNewVisitor());

            }
        }
    }
        
    



    /**
     * Make sure is new member and not left user
     * @return void
     */
    public function test_new_chat_member_status_not_equals_member_throws_exception(): void
    {
        foreach ($this->testObjects as $object) {

            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);
            if ($message instanceof StatusUpdateModel) {

                if ($object["chat_member"]["new_chat_member"]["status"] !== "member") {
                     $this->assertFalse($service->blockNewVisitor());

                }
            }
        }
    }



    public function test_new_user_invited_blocked_response_result_true(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);



            if ($message instanceof InvitedUserUpdateModel) {
                $result = $service->blockNewVisitor();
                if ($result === true) {
                    $this->assertTrue($result);
                }
            }
        }
    }




    public function test_change_status_by_admin_does_not_lead_to_unexpected_user_ban_and_model_type_is_not_invited_user_model(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);

            if($message->getType() === "chat_member") {

            if (
                $message->getFromAdmin() &&
                $object["chat_member"]["old_chat_member"]["status"] === "restricted" &&
                $object["chat_member"]["new_chat_member"]["status"] === "member"
            ) {
                
                $result = $service->blockNewVisitor();
                $this->assertFalse($result);
                $this->assertTrue($message instanceof StatusUpdateModel);
            }
            }
        }
    }
}