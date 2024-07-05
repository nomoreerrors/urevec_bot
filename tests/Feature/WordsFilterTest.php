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
    public function test_filter_array_contains_word_return_true(): void
    {
        $badWords = json_decode(Storage::get('badwords.json'), true);


        foreach ($this->testObjects as $object) {
            $message = (new BaseTelegramRequestModel($object))->create();
            $filter = new FilterService($message);
            $service = new TelegramBotService($message);



            if ($message instanceof TextMessageModel) {
                foreach ($badWords as $word) {

                    if (str_contains($message->getText(), mb_strtolower($word))) {
                        $result = $filter->wordsFilter();
                        $this->assertTrue($result);
                    }
                }
            }
        }
    }
}
