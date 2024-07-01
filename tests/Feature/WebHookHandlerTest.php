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

            if ($hasLink) {
                log::info($object);
                $response = $this->post("api/webhook", $this->service->data);
                // dd($response);
                // log::info($response->getOriginalContent());
                $this->assertTrue($response->getOriginalContent() === "user blocked");
            }
        }
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $this->service->checkMessageType();


            $isForwardMessage = $this->service->checkIfMessageForwardFromAnotherGroup();

            if ($isForwardMessage) {

                $response = $this->post("api/webhook", $object);
                // dd($response->getOriginalContent());
                $this->assertTrue($response->getOriginalContent() === "user blocked");
            }
        }
    }



    public function test_new_user_restricted_automatically(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $this->service->checkMessageType();
            $isNewMember = $this->service->checkIfIsNewMember();



            if ($isNewMember) {
                $response = $this->post("api/webhook", $this->service->data);

                // dd($response);
                if ($response->getOriginalContent() !== "default response") {
                    $this->assertTrue($response->getOriginalContent() === "new member blocked for 24 hours");
                }
            }
        }
    }
}
