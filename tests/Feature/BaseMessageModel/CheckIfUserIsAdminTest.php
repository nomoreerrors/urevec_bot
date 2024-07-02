<?php

namespace Tests\Feature\BaseMessageModel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MessageModel;

class CheckIfUserIsAdminTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_user_is_admin_return_true(): void
    {
        foreach ($this->testObjects as $object) {
            $message = new MessageModel($object);
            $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));

            if ((string)in_array($message->userId, $adminsIdArray)) {
                $this->assertTrue($message->userIsAdmin);
            }
        }
    }
}
