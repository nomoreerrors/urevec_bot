<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class CheckIfMessageForwardFromAnotherGroupTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_message_forwarded(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType === "message" || $messageType === "edited_message") {
                if (
                    array_key_exists("forward_from_chat", $object[$messageType]) &&
                    array_key_exists("forward_origin", $object[$messageType])
                ) {
                    $result = $this->service->checkIfMessageForwardFromAnotherGroup();

                    $this->assertEquals(true, $result);
                }

                if (
                    !array_key_exists("forward_from_chat", $object[$messageType]) &&
                    !array_key_exists("forward_origin", $object[$messageType])
                ) {
                    $result = $this->service->checkIfMessageForwardFromAnotherGroup();

                    $this->assertEquals(false, $result);
                }
            }
        }
    }
}
