<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;

class LinksFilterTest extends TestCase
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

    public function test_if_message_text_value_has_link_returns_true(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;

            $hasLink = false;
            $messageType = $this->service->checkMessageType();

            if (
                ($messageType === "message" || $messageType === "edited_message") &&
                (array_key_exists("text", $this->service->data[$messageType]))
            ) {

                $hasLink = strpos($this->service->data[$messageType]["text"], "http");
                if ($hasLink !== false) {
                    $this->assertTrue($this->service->linksFilter() !== 0);
                }
            }
        }
    }


    public function test_message_has_not_message_key_or_text_key_returns_false(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;


            $messageType = $this->service->checkMessageType();

            if ($messageType !== "message" || $messageType !== "edited_message") {
                $this->assertFalse($this->service->linksFilter());
            }
            if (!array_key_exists("text", $this->service->data[$messageType])) {
                $this->assertFalse($this->service->linksFilter());
            } {
            }
        }
    }


    public function test_message_text_value_has_not_links_returns_false(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;

            $hasLink = false;
            $messageType = $this->service->checkMessageType();

            if (
                ($messageType === "message" || $messageType === "edited_message") &&
                (array_key_exists("text", $this->service->data[$messageType]))
            ) {

                $hasLink = strpos($this->service->data[$messageType]["text"], "http");
                if ($hasLink === false) {
                    $this->assertFalse($this->service->linksFilter());
                }
            }
        }
    }
}
