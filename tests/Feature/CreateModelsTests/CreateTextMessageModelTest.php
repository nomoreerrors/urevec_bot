<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\MessageModels\TextMessageModel;
use App\Models\BaseTelegramRequestModel;

class CreateTextMessageModelTest extends TestCase
{
    public function testCreateTextMessageModel(): void
    {
        $data = $this->getTextMessageModelData();
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(TextMessageModel::class, $textMessageModel);
    }
}