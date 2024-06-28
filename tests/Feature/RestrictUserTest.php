<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Log;

class RestrictUserTest extends TestCase
{
    /**
     * Возвращается ли объект с описанием "user id not found"
     * Если возвращается, то и при верном id запрос работает
     * @return void
     */
    public function test_restrict_user_by_user_id_from_incoming_message_if_not_found_returns_user_not_found(): void
    {

        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType == "message" || $messageType == "edited_message") {

                $isAdmin = $this->service->checkIfUserIsAdmin();
                if (!$isAdmin) {

                    $response = $this->service->restrictUser(time() + 86400);

                    if (array_key_exists("description", $response)) {

                        $this->assertTrue($response["description"] === "Bad Request: user not found");
                    }
                }
            }
        }
    }
}
