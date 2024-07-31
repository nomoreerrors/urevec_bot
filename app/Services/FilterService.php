<?php

namespace App\Services;

use App\Models\MessageModels\MediaModels\BaseMediaModel;
use App\Models\MessageModels\TextMessageModel;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class FilterService
{
    public function __construct(private TelegramRequestModelBuilder $model)
    {
    }

    /**
     * Filter words for removal of profanity and advertising
     * @return bool
     */
    public function wordsFilter(): bool
    {
        if ($this->model->getFromAdmin()) {
            return false;
        }

        $text = $this->getText();

        if ($this->checkIfStringContainsUnusualChars($text)) {
            return true;
        }

        if (!$this->isMessageValid()) {
            return false;
        }

        $badWords = $this->getBadWords();
        $phrases = $this->getPhrases();

        if (empty($badWords) || empty($phrases)) {
            throw new Exception("Missing filter messages file storage/app/badwords.json or badPhrases.json");
        }

        $cleanedText = $this->cleanText($text);

        if ($this->containsPhrases($cleanedText, $phrases)) {
            $this->storeBadWord($cleanedText);
            return true;
        }

        $words = $this->getArrayOfWordsFromString($cleanedText);
        $longWords = $this->deleteShortWordsFromArray($words);
        // dd($longWords);


        foreach ($longWords as $word) {
            if (in_array(mb_strtolower($word), $badWords)) {
                $this->storeBadWord($word);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if string contains Chinese, Japanese etc characters
     * @param string $text
     * @return bool
     */
    private function checkIfStringContainsUnusualChars(string $text): bool
    {
        $patterns = ["/\p{Han}+/u", "/\p{Katakana}+/u", "/\p{Arabic}+/u"];
        $matches = array_map(function ($pattern) use ($text) {
            return preg_match($pattern, $text);
        }, $patterns);
        // dd($matches);

        if (array_filter($matches)) {
            return true;
        }
        return false;
    }

    private function isMessageValid(): bool
    {
        return ($this->model instanceof TextMessageModel) || ($this->model instanceof BaseMediaModel);
    }

    private function getBadWords(): array
    {
        return json_decode(Storage::get('badwords.json'), true);
    }

    private function getPhrases(): array
    {
        return json_decode(Storage::get('badPhrases.json'), true);
    }

    private function getText(): string
    {
        return method_exists($this->model, 'getText') ? $this->model->getText() : $this->model->getCaption();
    }

    /**
     *  Replace any remaining non-letters, numbers, or whitespace characters including EMOJI with a single space
     *  [^...] is a negated character class, meaning it will match any character that is not in the specified set of characters.
     *  \p{L} matches any letter.
     *  \p{N} matches any number.
     *  \p{P} matches any punctuation character.
     *  \s matches any whitespace character.
     *  \s+ matches one or more whitespace characters. 
     */
    private function cleanText(string $text): string
    {
        $text = str_replace("\n", " ", $text);

        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', " ", $text);

        // Convert to lowercase including Russian letters
        return mb_strtolower($text);
    }


    private function containsPhrases(string $cleanedText, array $phrases): bool
    {
        foreach ($phrases as $phrase) {
            if (str_contains($cleanedText, $phrase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store deleted word in file
     * @param string $text
     * @param mixed $word
     *  @return void
     */
    private function storeBadWord(string $word): void
    {
        Storage::append(
            'words_deleted_by_filter.txt',
            PHP_EOL . "FROM ID: " . $this->model->getFromId() . PHP_EOL .
            "WORD: " . $word
        );
    }

    /**
     * String to array of words
     * @param mixed $cleanedText
     * @return array
     */
    public function getArrayOfWordsFromString($cleanedText): array
    {
        return explode(' ', $cleanedText);
    }

    /**
     * Summary of deleteShortWordsFromArray
     *  The compared number should be more than "2" if string  is a unicode format
     * @param array $cleanedText
     * @return array
     */
    private function deleteShortWordsFromArray(array $cleanedText): array
    {
        foreach ($cleanedText as $key => $value) {

            if (strlen($value) <= 2) {
                unset($cleanedText[$key]);
            }
        }
        return $cleanedText;
    }
}
