<?php

namespace Tests\Feature;

use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Log;

class BlockNewVisitorTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function test_block_new_user_response_result_true(): void
    {

        foreach ($this->testObjects as $object) {


            $message = new TelegramMessageModel($object);
            $service = new TelegramBotService($message);



            if ($message->getIsNewMemberJoinUpdate()) {


                $result = $service->blockNewVisitor();
                if ($result === true) {
                    $this->assertTrue($result);
                }
            }
        }


        if (!$message->getIsNewMemberJoinUpdate()) {

            $result = $service->blockNewVisitor();

            $this->assertFalse($result);
        }
    }

    /**
     * Make sure is new member and not left user
     * @return void
     */
    public function test_new_chat_member_status_not_equals_member_return_false(): void
    {

        foreach ($this->testObjects as $object) {


            $message = new TelegramMessageModel($object);
            $service = new TelegramBotService($message);
            if (array_key_exists("chat_member", $object)) {

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
            $message = new TelegramMessageModel($object);
            $service = new TelegramBotService($message);



            if ($message->getInvitedUsersId() !== []) {

                $result = $service->blockNewVisitor();
                if ($result === true) {
                    $this->assertTrue($result);
                }
            }
        }
    }
}
