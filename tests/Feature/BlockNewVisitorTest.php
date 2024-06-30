<?php

namespace Tests\Feature;

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
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();
            if (
                $messageType === "chat_member" &&
                array_key_exists("new_chat_member", $object[$messageType]) &&
                $object[$messageType]["new_chat_member"]["user"]["id"] === $object[$messageType]["from"]["id"]
            ) {

                if ($object[$messageType]["new_chat_member"]["status"] === "member") {

                    $result = $this->service->blockNewVisitor();
                    if ($result === true) {
                        $this->assertTrue($result);
                    }
                }


                if ($object[$messageType]["new_chat_member"]["status"] !== "member") {

                    $result = $this->service->blockNewVisitor();
                    $this->assertFalse($result);
                }

                if ($object[$messageType]["new_chat_member"]["status"] === "left") {
                    //Просто для верности доп. проверка
                    $result = $this->service->blockNewVisitor();
                    $this->assertFalse($result);
                }
            }
        }
    }
}
