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
    public function testTextMessageModelContainsBlackListWordsWithUpperCaseLettersReturnsTrue(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "модерАторы";
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }


    public function testMediaModelCaptionlContainsBlackListWordsWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "бессмысленный текст и запретное слово: админИСТРаторы";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->getModel();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());

    }



    public function testTextMessageModelContainsBlackListPhrasesWithUpperCaseLettersReturnsTrue(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "в сосЕднюю Группу";
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }



    public function testMediaModelCaptionlContainsBlackListPhrasesWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "бессмысленный текст и запретная фраза: сдАЕтся в Аренду";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->getModel();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());

    }

    public function testCannotDeleteAdministratorMessage()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $messageModel = (new BaseTelegramRequestModel($data))->getModel();

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

        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $filterService = new FilterService($textMessageModel);
        $this->assertTrue($filterService->wordsFilter());
    }

}
