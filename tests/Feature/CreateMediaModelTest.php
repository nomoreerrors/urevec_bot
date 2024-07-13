<?php

namespace Tests\Feature;

use App\Models\ForwardMessageModel;
use App\Models\MultiMediaModel;
use App\Models\PhotoMediaModel;
use App\Models\VideoMediaModel;
use App\Models\VoiceMediaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramBotService;

class CreateMediaModelTest extends TestCase
{

    public function test_create_correct_media_model(): void
    {
        foreach ($this->testObjects as $object) {

            $message = (new BaseTelegramRequestModel($object))->create();

            $type = $message->getType();

            if (!array_key_exists("forward_from_chat", $object[$type])) {

                if (
                    array_key_exists("video", $object[$type]) &&
                    !array_key_exists("photo", $object[$type])
                ) {
                    $this->assertInstanceOf(VideoMediaModel::class, $message);
                }

                if (
                    array_key_exists("photo", $object[$type]) &&
                    !array_key_exists("video", $object[$type])
                ) {
                    $this->assertInstanceOf(PhotoMediaModel::class, $message);
                }

                if (
                    array_key_exists("photo", $object[$type]) &&
                    array_key_exists("video", $object[$type])
                ) {
                    $this->assertInstanceOf(MultiMediaModel::class, $message);
                }

                if (array_key_exists("voice", $object[$type])) {
                    $this->assertInstanceOf(VoiceMediaModel::class, $message);
                }
            }

        }
    }


    public function test_if_message_is_forward_return_model_is_not_media_type(): void
    {
        foreach ($this->testObjects as $object) {

            $message = (new BaseTelegramRequestModel($object))->create();

            $type = $message->getType();

            if (
                array_key_exists("forward_from_chat", $object[$type]) &&
                (
                    array_key_exists("video", $object[$type]) ||
                    array_key_exists("voice", $object[$type]) ||
                    array_key_exists("photo", $object[$type])
                )
            ) {

                $this->assertInstanceOf(ForwardMessageModel::class, $message);
            }
        }

    }
}