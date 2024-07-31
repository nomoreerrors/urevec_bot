<?php

namespace Tests\Feature\CreateModelsTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\MessageModels\TextMessageModel;
use Tests\TestCase;

class TextMessageModelTest extends TestCase
{
    // Test method to test the setIsCommand method
    public function testSetIsCommand()
    {
        // Create an instance of TextMessageModel with a sample data
        $data = [
            "update_id" => 24024902,
            "message" => [
                "message_id" => 17770,
                "chat" => [
                    "id" => -1002222230714,
                    "type" => "supergroup",
                    "title" => "Rahat Lukum"
                ],
                "from" => [
                    "id" => $this->getTestUserId(),
                    "first_name" => "Mai Abrikosov"
                ],
                "text" => "/command"
            ]
        ];
        $textMessageModel = new TextMessageModel($data);

        // Test when the text starts with a command
        $this->assertTrue($textMessageModel->getIsCommand());

        // Test when the text does not start with a command
        $data["message"]["text"] = "This is not a command";
        $textMessageModel = new TextMessageModel($data);
        $this->assertFalse($textMessageModel->getIsCommand());
    }
}
