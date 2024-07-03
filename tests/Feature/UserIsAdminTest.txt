<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;



class UserIsAdminTest extends TestCase
{




    public function test_if_user_is_admin_return_true(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType === "message" || $messageType === "edited_message") {
                if (in_array((string)$object[$messageType]["from"]["id"], $this->adminsIdArray)) {
                    log::info("admin id: ", $object);

                    $this->assertTrue($this->service->checkIfUserIsAdmin());
                }
            }
        }
    }


    public function test_if_user_is_not_admin_return_false(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType == "message" || $messageType == "edited_message") {
                if (!in_array($object[$messageType]["from"]["id"], $this->adminsIdArray))
                    $this->assertFalse($this->service->checkIfUserIsAdmin());
            }
        }
    }
}
