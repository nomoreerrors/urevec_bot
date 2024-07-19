<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FilterService;
use App\Models\BaseTelegramRequestModel;

class FilterServiceTest extends TestCase
{
    /**
     * Test that WordsFilter method returns true if not an administrator message contains words from badWords.json
     * in case when the model instanceof  TextmessageModel
     * @return void
     */
    public function testTextMessageModelContainsBlackListWordsWithUpperCaseLettersReturnsTrue(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "модерАторы";
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }

    /**
     * Test that WordsFilter method returns true if not an administrator message contains phrases from badPhrases.json
     * in case when the model instanceof  TextmessageModel
     * @return void
     */
    public function testTextMessageModelContainsBlackListPhrasesWithUpperCaseLettersReturnsTrue(): void
    {
        $data = $this->getTextMessageModel()->getData();
        $data["message"]["text"] = "в сосЕднюю Группу";
        $textMessageModel = (new BaseTelegramRequestModel($data))->getModel();
        $filter = new FilterService($textMessageModel);

        $this->assertTrue($filter->wordsFilter());
    }

    /**
     * Test that WordsFilter method returns true if not an administrator message contains phrases from badPhrases.json
     * in case when the model instanceof  BaseMediaMessageModel and  has a caption key instead of text key
     * @return void
     */
    public function testMediaModelCaptionlContainsBlackListPhrasesWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "бессмысленный текст и запретная фраза: сдАЕтся в Аренду";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->getModel();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());
    }

    /**
     * Test that WordsFilter method returns true if not an administrator message contains words from badWords.json
     * in case when the model instanceof  BaseMediaMessageModel and  has a caption key instead of text key
     * @return void
     */
    public function testMediaModelCaptionlContainsBlackListWordsWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["caption"] = "Продаю свойский чеснок,сорт Грибоаский,можно на еду,на хранение и на посадку.Цена за 1 кг 300 руб, от трех кг по 250р.Все вопросы в личку. ";
        $mediaMessageModel = (new BaseTelegramRequestModel($data))->getModel();

        $filter = new FilterService($mediaMessageModel);
        $this->assertTrue($filter->wordsFilter());

    }

    /**
     * Test that WordsFilter method  wouldn't check if administrator's message contains  any of the banned words and chars
     * or phrases and returns false.
     * and asian symbols etc.
     * @return void
     */
    public function testWordsFilterDoesNotCheckingAdministratorsMessageAndReturnsFalse()
    {
        $data = $this->getMultiMediaModel()->getData();
        $data["message"]["from"]["id"] = $this->getAdminId();
        $data["message"]["caption"] = "проДам bla-bla-bla https://t.me/telegram  Arabic: ب تاء , Chinese: 我你 , Japanese: すせ";

        $messageModel = (new BaseTelegramRequestModel($data))->getModel();

        $filter = new FilterService($messageModel);
        $this->assertFalse($filter->wordsFilter());
    }

    /**
     * Test that WordsFilter returns true if the text contains words from badWords.json
     * even if some symbols are uppercase 
     * @return void
     */
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
     * Avoiding Chinese, Japanese and Arabic messages
     * Testcase of the WordsFilter function
     * @return void
     */
    public function testIfStringContainsUnusualCharsWordsFilterReturnsTrue(): void
    {
        $model = $this->getTextMessageModel();

        //Test case where the text does not contains Chinese, Japanese or Arabic characters and banned words
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
