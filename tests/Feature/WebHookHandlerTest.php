<?php

namespace Tests\Feature;

use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\LoggedExceptionCollection;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\ForwardMessageModel;
use Exception;
use App\Models\BaseTelegramRequestModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\TextMessageModel;
use Error;

class WebHookHandlerTest extends TestCase
{

    public function test_if_message_contains_link_ban_user(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();


            if ($message instanceof TextMessageModel && !$message->getFromAdmin()) {

                if ($message->getHasLink() || $message->hasTextLink()) {

                    $response = $this->post("api/webhook", $object);

                    try {
                        log::info($response->getOriginalContent());
                        $this->assertTrue($response->getOriginalContent() === "user blocked");
                        // dd($response);
                    } catch (Exception $e) {
                        dd($object);
                    }
                }
            }
        }
    }

    public function test_if_is_forward_message_from_another_group_ban_user(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();

            if ($message instanceof ForwardMessageModel) {

                // try {
                if (!$message->getFromAdmin()) {

                    $response = $this->post("api/webhook", $object);
                    $this->assertTrue($response->getOriginalContent() === "user blocked");
                }


                if ($message->getFromAdmin()) {

                    $response = $this->post("api/webhook", $object);
                    $this->assertTrue($response->getOriginalContent() === "default response");
                }
            }
        }
    }



    public function test_new_user_restricted_automatically(): void
    {
        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();


            if ($message instanceof NewMemberJoinUpdateModel) {
                $response = $this->post("api/webhook", $object);

                if ($response->getOriginalContent() !== "default response") {

                    $this->assertTrue($response->getOriginalContent() === "new member blocked for 24 hours");
                }
            }


            if ($message instanceof InvitedUserUpdateModel) {
                $response = $this->post("api/webhook", $object);

                if ($response->getOriginalContent() !== "default response") {
                    $this->assertTrue($response->getOriginalContent() === "new member blocked for 24 hours");
                }
            }
        }
    }
}
