<?php

namespace Tests\Feature;

use App\Models\BaseTelegramRequestModel;
use App\Models\TextMessageModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;

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
            $message = (new BaseTelegramRequestModel($object))->create();
            $service = new TelegramBotService($message);

            if ($message instanceof TextMessageModel) {

                $response = $service->deleteMessage();

                $this->assertFalse($response);
            }
        }
    }
}
