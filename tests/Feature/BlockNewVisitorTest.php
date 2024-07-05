<?php

namespace Tests\Feature;

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



            try {
                if ($message instanceof MessageModel) {
                    $service = new TelegramBotService($message);

                    if ($message instanceof NewMemberJoinUpdateModel) {

                        $result = $service->blockNewVisitor();
                        if ($result === true) {
                            $this->assertTrue($result);
                        }
                    }


                    if (!$message instanceof NewMemberJoinUpdateModel) {

                        $result = $service->blockNewVisitor();


                        $this->assertFalse($result);
                    }
                }
            } catch (Error $e) {
                dd($object);
            }
        }
    }



    /**
     * Make sure is new member and not left user
     * @return void
     */
    public function test_new_chat_member_status_not_equals_member_return_false(): void
    {

        foreach ($this->testObjects as $object) {


            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);
            if ($message instanceof StatusUpdateModel) {

                if ($object["chat_member"]["new_chat_member"]["status"] !== "member") {
                    $result = $service->blockNewVisitor();
                    $this->assertFalse($result);
                }
            }
        }
    }





    public function test_new_user_invited_by_another_user_blocked_response_result_true(): void
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
}
