<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\TelegramBotService;
use Hamcrest\Arrays\IsArray;
use Illuminate\Support\Facades\Storage;

class UserIsAdminTest extends TestCase
{
    protected array $testObjects;

    protected $service;

    protected array $adminsIdArray;



    protected function setUp(): void
    {
        parent::setUp();
        $this->testObjects = json_decode(file_get_contents(__DIR__ . "/../TestObjects.json"), true);
        $this->service = new TelegramBotService();
        $this->adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
    }



    public function test_if_user_is_admin_return_true(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();


            if ($messageType == "message" || $messageType == "edited_message") {
                if (in_array($object[$messageType]["from"]["id"], $this->adminsIdArray))
                    $this->assertTrue($this->service->checkIfUserIsAdmin());
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
