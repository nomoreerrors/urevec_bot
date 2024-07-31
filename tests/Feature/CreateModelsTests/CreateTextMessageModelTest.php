<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\MessageModels\TextMessageModel;
use App\Models\TelegramRequestModelBuilder;

class CreateTextMessageModelTest extends TestCase
{
    public function testCreateTextMessageModel(): void
    {
        $data = $this->getTextMessageModelData();
        $textMessageModel = (new TelegramRequestModelBuilder($data))->create();
        $this->assertInstanceOf(TextMessageModel::class, $textMessageModel);
    }
}