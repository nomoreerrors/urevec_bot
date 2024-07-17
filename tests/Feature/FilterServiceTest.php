<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FilterService;
use App\Models\BaseTelegramRequestModel;

class FilterServiceTest extends TestCase
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

    /**
     * Avoiding spam Chinese, Japanese and Arabic messages
     * @return void
     */
    public function testIfStringContainsUnusualCharsReturnsTrue(): void
    {
        //Text without any bad word, link or unusual char
        $model = $this->getTextMessageModel();
        $filterService = new FilterService($model);

        $this->assertFalse($filterService->wordsFilter());

        $data = $model->getData();

        // Test case where the text contains Chinese characters
        $data["message"]["text"] = "赚.钱";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $filterService = new FilterService($model);
        $this->assertTrue($filterService->wordsFilter());


        // Test case where the text contains Japanese characters
        $data["message"]["text"] = "ナ";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $filterService = new FilterService($model);
        $this->assertTrue($filterService->wordsFilter());


        // Test case where the text contains Arabic characters
        $data["message"]["text"] = "وف العر";
        $model = (new BaseTelegramRequestModel($data))->getModel();
        $filterService = new FilterService($model);
        $this->assertTrue($filterService->wordsFilter());
    }
}
