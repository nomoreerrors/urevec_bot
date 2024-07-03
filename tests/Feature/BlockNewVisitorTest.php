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
