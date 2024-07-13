<?php

namespace Tests\Feature;

use App\Models\TextMessageModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\FilterService;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramBotService;
use App\Models\BaseTelegramRequestModel;
use Exception;
use App\Services\Filter;

class WordsFilterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_text_message_model_contains_black_list_word_return_true(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "модераторы";
        $textMessageModel = (new BaseTelegramRequestModel($data))->create();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }


    public function test_media_model_caption_contains_black_list_word_return_true()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "бессмысленный текст и запретное слово: администраторы";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->create();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());

    }



    public function test_text_message_model_contains_black_list_phrases_return_true(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "в соседнюю группу";
        $textMessageModel = (new BaseTelegramRequestModel($data))->create();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }



    public function test_media_model_caption_contains_black_list_phrase_return_true()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "бессмысленный текст и запретная фраза: сдается в аренду";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->create();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());

    }

    public function test_not_able_to_delete_administrator_message()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $messageModel = (new BaseTelegramRequestModel($data))->create();
        $filter = new FilterService($messageModel);
        $this->assertFalse($filter->wordsFilter());
    }

    public function testTextContainsUpperCaseBlackListWordsFilterReturnsTrue()
    {
        $data = [
            "update_id" => 267308067,
            "message" => [
                "message_id" => 18608,
                "from" => [
                    "id" => 1074023376,
                    "first_name" => "Устин Акимыч"
                ],
                "chat" => [
                    "id" => -2222444424,
                ],
                "text" => "Админ чата кто?",
            ],
        ];

        $textMessageModel = (new BaseTelegramRequestModel($data))->create();
        $filterService = new FilterService($textMessageModel);
        $this->assertTrue($filterService->wordsFilter());
    }

}
