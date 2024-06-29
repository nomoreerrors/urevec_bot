<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class WebHookHandlerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_message_contains_link_ban_user(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $this->service->checkMessageType();

            $hasLink = $this->service->linksFilter();
            // log::info($this->data);
            if ($hasLink) {
                log::info($object);
                $response = $this->post("api/webhook", $this->service->data);
                // dd($response);
                log::info($response->getOriginalContent());
                $this->assertTrue($response->getOriginalContent() === "user blocked");
            }
        }
    }



    public function test_new_user_restricted_for_24_hours(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType === "message" || $messageType === "edited_message") {

                if (array_key_exists("new_chat_participant", $this->service->data[$messageType])) {
                    $response = $this->post("api/webhook", $this->service->data);
                    if ($response->getOriginalContent() !== "default response") {

                        $this->assertTrue($response->getOriginalContent() === "new member blocked for 24 hours");
                    }
                }
            }
        }
    }
}
