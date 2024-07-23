<?php

namespace Tests\Feature;

use App\Models\BaseMediaModel;
use App\Models\ForwardMessageModel;
use App\Models\MessageModel;
use App\Models\TextMessageModel;
use Tests\TestCase;
use App\Models\BaseTelegramRequestModel;

class CreateMessageModelTest extends TestCase
{
    public function testCreateMessageModel(): void
    {
        $data = $this->getMessageModelData();
        $messageModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertTrue(get_class($messageModel) === MessageModel::class);

        //test if has text key model is not a pure message model
        $data["message"]["text"] = "test";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(TextMessageModel::class, $model);

        unset($data["message"]["text"]);

        //test if has video key model is not a pure message model
        $data["message"]["video"] = "test";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(BaseMediaModel::class, $model);

        unset($data["message"]["video"]);

        //test if has forward_origin key model is not a pure message model
        $data["message"]["forward_origin"] = [];
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(ForwardMessageModel::class, $model);
    }
}