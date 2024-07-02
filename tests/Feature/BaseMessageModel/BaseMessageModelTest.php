<?php

namespace Tests\Feature\BaseMessageModel;

use App\Models\MessageModel;
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
            $message = new MessageModel($object);

            $this->assertNotEmpty($message->messageType);
            $this->assertNotEmpty($message->userId);






            if (
                ($message->messageType === "message" ||
                    $message->messageType === "edited_message")
            ) {
                if (array_key_exists("text", $object[$message->messageType])) {
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
