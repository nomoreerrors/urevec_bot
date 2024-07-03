<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckIfIsNewMemberTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_new_chat_member_status_return_bool(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType === "chat_member") {

                if (array_key_exists("new_chat_member", $object[$messageType])) {
                    if ($object[$messageType]["new_chat_member"]["status"] === "member") {
                        $result = $this->service->checkIfIsNewMember();
                        $this->assertTrue($result);
                    }

                    if ($object[$messageType]["new_chat_member"]["status"] !== "member") {
                        $result = $this->service->checkIfIsNewMember();
                        $this->assertFalse($result);
                    }
                }
            }
        }
    }
}
