<?php

namespace Tests\Feature;

use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;
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
            $message = new TelegramMessageModel($object);
            $service = new TelegramBotService($message);



            if ($message->getHasLink()) {
                $response = $this->post("api/webhook", $object);
                $this->assertTrue($response->getOriginalContent() === "user blocked");
            }
        }
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        foreach ($this->testObjects as $object) {
            $message = new TelegramMessageModel($object);
            $service = new TelegramBotService($message);


            if ($message->getIsForwardMessage()) {

                $response = $this->post("api/webhook", $object);
                // dd($response->getOriginalContent());
                $this->assertTrue($response->getOriginalContent() === "user blocked");
            }
        }
    }



    public function test_new_user_restricted_automatically(): void
    {
        foreach ($this->testObjects as $object) {
            $message = new TelegramMessageModel($object);


            if ($message->getIsNewMemberJoinUpdate()) {
                $response = $this->post("api/webhook", $object);

                // dd($response);
                if ($response->getOriginalContent() !== "default response") {
                    $this->assertTrue($response->getOriginalContent() === "new member blocked for 24 hours");
                }
            }
        }
    }
}
