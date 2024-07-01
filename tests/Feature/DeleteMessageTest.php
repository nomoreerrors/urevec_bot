<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteMessageTest extends TestCase
{
    /**
     * Базовые методы в классе TestCase.
     */


    /**
     * Несуществующее сообщение. Если ответ сервера "message to delete not found",
     * то запрос правильный и при верном message_id успешно удалит сообщение
     * @return void
     */
    public function test_messsage_id_not_found_return_description_key_value_equals_message_not_found(): void
    {
        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();

            if ($messageType === "message" || $messageType === "edited_message") {
                $response = $this->service->deleteMessage();

                if (array_key_exists("description", $response)) {
                    $this->assertTrue($response["description"] === "Bad Request: message to delete not found");
                }
            }
        }
    }
}
