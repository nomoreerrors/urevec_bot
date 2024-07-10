<?php

namespace Tests\Feature;

use App\Exceptions\TelegramModelException;
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
use App\Services\CONSTANTS;
use App\Services\ErrorMessages;
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
                    // dd($response);

                    try {
                        // dd($response);
                        log::info($response->getOriginalContent());
                        $this->assertTrue($response->getOriginalContent() === CONSTANTS::MEMBER_BLOCKED);
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
                    $this->assertTrue($response->getOriginalContent() === CONSTANTS::MEMBER_BLOCKED);
                }


                if ($message->getFromAdmin()) {

                    $response = $this->post("api/webhook", $object);
                    $this->assertTrue($response->getOriginalContent() === CONSTANTS::DEFAULT_RESPONSE);
                }
            }
        }
    }



    public function test_new_user_restricted_automatically(): void
    {
        foreach ($this->testObjects as $object) {
            try {

                $message = (new BaseTelegramRequestModel($object))->create();
                if ($message === null) {
                    log::info(json_encode($message));
                }
            } catch (TelegramModelException $e) {

                dd($e->getInfo(), $e->getData()); 
            }


            if ($message instanceof NewMemberJoinUpdateModel) {
                // dd("here");
                $response = $this->post("api/webhook", $object);

                if ($response->getOriginalContent() !== CONSTANTS::DEFAULT_RESPONSE) {

                    $this->assertTrue($response->getOriginalContent() === CONSTANTS::NEW_MEMBER_RESTRICTED);
                }
            }


            if ($message instanceof InvitedUserUpdateModel) {

                $response = $this->post("api/webhook", $object);

                // dd($response);
                if ($response->getOriginalContent() !== CONSTANTS::DEFAULT_RESPONSE) {
                    $this->assertTrue($response->getOriginalContent() === CONSTANTS::NEW_MEMBER_RESTRICTED);
                }
            }
        }
    }
}
