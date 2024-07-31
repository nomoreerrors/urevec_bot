<?php

namespace Tests\Feature;

use App\Models\MessageModels\MediaModels\VideoMediaModel;
use App\Models\MessageModels\MessageModel;
use App\Models\MessageModels\TextMessageModel;
use App\Models\MessageModels\MediaModels\BaseMediaModel;
use Tests\TestCase;
use App\Models\TelegramRequestModelBuilder;

class CreateMessageModelTest extends TestCase
{
    public function testCreateMessageModel(): void
    {
        $data = $this->getMessageModelData();
        $messageModel = (new TelegramRequestModelBuilder($data))->create();
        $this->assertTrue(get_class($messageModel) === MessageModel::class);

        //test if has text key model is not a pure message model
        $data["message"]["text"] = "test";
        $model = (new TelegramRequestModelBuilder($data))->create();
        $this->assertInstanceOf(TextMessageModel::class, $model);

        unset($data["message"]["text"]);

        //test if has video key model is not a pure message model
        $data["message"]["video"] = "test";
        $model = (new TelegramRequestModelBuilder($data))->create();
        $this->assertInstanceOf(VideoMediaModel::class, $model);
    }
}