<?php

namespace Tests\Feature\BaseMessageModel;

use App\Models\MessageModel;
use App\Models\TelegramMessageModel;
use ErrorException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BaseMessageModelTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_new_base_test_model_object_set_correct_values(): void
    {
        $links = ["http", ".рф", ".ру", ".ком", ".com", ".ru"];


        // $message = new MessageModel($this->testObjects["20"]);
        // dd($message);

        foreach ($this->testObjects as $object) {
            $message = new TelegramMessageModel($object);


            $this->assertNotEmpty($message->getType());

            if ($message->getType() === "edited_message" || $message->getType() === "message") {
                $this->assertNotEmpty($message->getFromId());
            }


            $messageType = "";

            if (array_key_exists("message", $object)) {
                $messageType = "message";
            }

            if (array_key_exists("edited_message", $object)) {
                $messageType = "edited_message";
            }

            if (array_key_exists("chat_member", $object)) {
                $messageType = "chat_member";
            }

            if (array_key_exists("my_chat_member", $object)) {
                $messageType = "my_chat_member";
            }



            if (
                ($message->messageType === "message" ||
                    $message->messageType === "edited_message")
            ) {
                if (array_key_exists("text", $object[$messageType])) {
                    $this->assertNotEmpty($message->text);


                    foreach ($links as $link) {

                        $hasLink = str_contains($message->text, $link);

                        if ($hasLink) {
                            $this->assertTrue($message->hasLink);
                        }
                    }
                }



                if (array_key_exists("entities", $object[$message->messageType])) {
                    $this->assertNotEmpty($message->entities);

                    for ($i = 0; $i < count($message->entities); $i++) {
                        if (array_key_exists("type", $object[$message->messageType]["entities"][$i])) {
                            if ($message->entities[$i]["type"] === "url" || $message->entities[$i]["type"] === "text_link") {

                                $this->assertNotEmpty($message->hasTextLink);
                            }
                        }
                    }
                }
            }
        }
    }
}
