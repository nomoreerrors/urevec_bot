<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\FilterService;
use Illuminate\Support\Facades\Storage;
use App\Models\TelegramMessageModel;
use App\Services\TelegramBotService;
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
            $message = new TelegramMessageModel($object);
            $filter = new FilterService($message);


            if (!empty($message->getText())) {
                foreach ($badWords as $word) {
                    if (str_contains($message->getText(), $word)) {

                        $result = $filter->wordsFilter();
                        $this->assertTrue($result);
                    }
                }
            }
        }
    }
}
