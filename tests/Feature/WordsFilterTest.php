<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
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

        if ($badWords === null) {
            throw new Exception("Отстутствует файл фильтра сообщений storage/app/badwords.json");
        }

        foreach ($this->testObjects as $object) {
            $this->service->data = $object;
            $messageType = $this->service->checkMessageType();

            if (($messageType === "message" || $messageType === "edited_message") &&
                array_key_exists("text", $object[$messageType])
            ) {
                $text = $object[$messageType]["text"];


                foreach ($badWords as $word) {
                    if (str_contains($text, $word)) {

                        $result = $this->filter->wordsFilter($object);
                        $this->assertTrue($result);
                    }
                }
            }
        }
    }
}
