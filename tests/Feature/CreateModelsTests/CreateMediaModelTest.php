<?php

namespace Tests\Feature;

use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\MediaModels\MultiMediaModel;
use App\Models\MessageModels\MediaModels\VideoMediaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\MessageModels\MediaModels\PhotoMediaModel;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramBotService;
use App\Models\MessageModels\MediaModels\VoiceMediaModel;

class CreateMediaModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->data = $this->getMessageModelData();
    }

    public function test_create_video_media_model(): void
    {
        $this->data["message"]["video"] = [];
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->assertInstanceOf(VideoMediaModel::class, $model);
    }

    public function test_create_photo_media_model(): void
    {
        $this->data["message"]["photo"] = [];
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->assertInstanceOf(PhotoMediaModel::class, $model);
    }

    public function test_create_voice_media_model(): void
    {
        $this->data["message"]["voice"] = [];
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->assertInstanceOf(VoiceMediaModel::class, $model);
    }

    public function test_create_multi_media_model(): void
    {
        $this->data["message"]["video"] = [];
        $this->data["message"]["photo"] = [];
        $model = (new TelegramRequestModelBuilder($this->data))->create();
        $this->assertInstanceOf(MultiMediaModel::class, $model);
    }

}