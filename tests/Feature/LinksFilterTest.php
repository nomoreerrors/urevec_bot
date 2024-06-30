<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Log;

class LinksFilterTest extends TestCase
{
    public function test_if_message_text_value_has_link_returns_true(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;

            $hasLink = false;
            $messageType = $this->service->checkMessageType();


            if ($messageType === "message" || $messageType === "edited_message") {

                if (array_key_exists("entities", $this->service->data[$messageType])) {
                    if ($this->service->data[$messageType]["entities"][0]["type"] === "text_link") {

                        $this->assertTrue($this->service->linksFilter());
                    }
                }

                if (array_key_exists("text", $this->service->data[$messageType])) {

                    $hasLink = str_contains($this->service->data[$messageType]["text"], "http");
                    if ($hasLink) {


                        $this->assertTrue($this->service->linksFilter());
                    }
                }
            }
        }
    }

    public function test_message_has_not_message_key_or_text_key_returns_false(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;


            $messageType = $this->service->checkMessageType();

            if (
                $messageType !== "message" &&
                $messageType !== "edited_message"
            ) {

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
                (array_key_exists("text", $this->service->data[$messageType]) &&
                    !array_key_exists("entities", $this->service->data[$messageType]))

            ) {


                $hasLink = str_contains($this->service->data[$messageType]["text"], "http");

                if ($hasLink === false) {
                    log::info($object);
                    $this->assertFalse($this->service->linksFilter());
                }
            }

            if (array_key_exists("entities", $this->service->data[$messageType])) {
                if ($this->service->data[$messageType]["entities"][0]["type"] !== "text_link") {

                    $this->assertFalse($this->service->linksfilter());
                }
            }
        }
    }
}
