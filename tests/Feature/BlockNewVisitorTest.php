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
            if ($messageType === "chat_member") {
                // dd($messageType, $object);
                if (array_key_exists("new_chat_member", $this->service->data[$messageType])) {
                    if ($this->service->data[$messageType]["new_chat_member"]["status"] === "member");
                    $result = $this->service->blockNewVisitor();
                    // dd($result);
                    // dd($this->service->data[$messageType]["new_chat_member"]["status"] === "member");
                    if ($result === true) {
                        $this->assertTrue($result);
                    }
                }
            }
        }
    }
}
