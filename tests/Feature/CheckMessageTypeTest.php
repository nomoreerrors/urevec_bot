<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckMessageTypeTest extends TestCase
{


    public function test_assert_the_right_string_key_returned(): void
    {

        foreach ($this->testObjects as $object) {
            $this->service->data = $object;

            if (array_key_exists("message", $object)) {
                $this->assertEquals("message", $this->service->checkMessageType());
            } elseif (array_key_exists("edited_message", $object)) {
                $this->assertEquals("edited_message", $this->service->checkMessageType());
            } elseif (array_key_exists("my_chat_member", $object)) {
                $this->assertEquals("my_chat_member", $this->service->checkMessageType());
            } else {
                $this->assertEquals("unknown message type", $this->service->checkMessageType());
            }
        }
    }
}
