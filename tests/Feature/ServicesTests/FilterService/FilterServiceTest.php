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
        $data = $this->getTextMessageModelData();
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
        $data = $this->getTextMessageModelData();
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
    public function testMediaModelCaptionContainsBlackListPhrasesWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModelData();
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
    public function testMediaModelCaptionContainsBlackListWordsWithUpperCaseLettersReturnsTrue()
    {
        $data = $this->getMultiMediaModelData();
        $data["message"]["caption"] = "Добрый вечер,продАЕтся козье молоко,очень вкусное 0‘5 60 р";
        // dd($data);
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
        $data = $this->getMultiMediaModelData();
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
                    "id" => -1002222230714,
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
        $data = $this->getTextMessageModelData();
        $model = (new BaseTelegramRequestModel($data))->getModel();

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
//   "3024": {
//     "update_id": 267309185,
//     "message": {
//         "message_id": 18947,
//         "from": {
//             "id": 5583632952,
//             "is_bot": false,
//             "first_name": "Любовь Евгеньевна",
//             "last_name": "Будылкина"
//         },
//         "chat": {
//             "id": -1001522860812,
//             "title": "Чат | Микрорайон Юрьевец",
//             "type": "supergroup"
//         },
//         "date": 1721494457,
//         "text": "Добрый вечер,продается козье молоко,очень вкусное 0‘5 60 р",
//         "has_protected_content": true
//     },
//     "Moscow_time": "July 20, 2024, 7:54 pm"
// },

