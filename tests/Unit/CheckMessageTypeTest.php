<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\TelegramBotService;
use ErrorException;
use Exception;
use Hamcrest\Arrays\IsArray;
use Illuminate\Support\Facades\Storage;

class CheckMessageTypeTest extends TestCase
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
