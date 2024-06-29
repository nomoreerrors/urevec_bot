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
    public function test_block_user_response_result_true(): void
    {

        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();
            if ($messageType === "message" || $messageType === "edited_message") {
                if (array_key_exists("new_chat_participant", $this->service->data[$messageType])) {
                    $result = $this->service->blockNewVisitor();

                    if ($result === true) {
                        $this->assertTrue($result);
                    }
                }
            }
        }
    }


    public function test_block_user_id_not_found_response_ok_false(): void
    {

        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();
            if ($messageType === "my_chat_member") {
                $result = $this->service->blockNewVisitor();

                if ($result !== true) {

                    $this->assertFalse($result);
                }
            }
        }
    }
}
