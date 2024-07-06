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
        // $badWords = json_decode(Storage::get('badwords.json'), true);


        $message = (new BaseTelegramRequestModel($this->testObjects["15"]))->create();
        $filter = new FilterService($message);


        if ($message instanceof TextMessageModel) {

            $result = $filter->wordsFilter();
            $this->assertTrue($result);
        }
    }
}
