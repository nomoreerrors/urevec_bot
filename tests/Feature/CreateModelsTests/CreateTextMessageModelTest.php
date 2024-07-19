<?php

namespace Tests\Feature;

use App\Models\TextMessageModel;
use Tests\TestCase;
use App\Models\BaseTelegramRequestModel;

class CreateTextMessageModelTest extends TestCase
{
    public function testCreateTextMessageModel(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $this->assertInstanceOf(TextMessageModel::class, $textMessageModel);
    }
}