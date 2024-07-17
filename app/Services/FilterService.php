<?php

namespace App\Services;

use App\Models\BaseMediaModel;
use App\Models\TextMessageModel;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BaseTelegramRequestModel;
use App\Models\TelegramMessageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class FilterService
{
    public function __construct(private BaseTelegramRequestModel $model)
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
            $this->storeDeletedWord($text, $cleanedText);
            return true;
        }

        $words = $this->getWordsFromText($cleanedText);

        foreach ($words as $word) {
            if (in_array(mb_strtolower($word), $badWords)) {
                $this->storeDeletedWord($text, $word);
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

    private function cleanText(string $text): string
    {
        $text = str_replace(
            [
                '.',
                ',',
                '!',
                '?',
                '&',
                '/',
                '"',
                '(',
                ')',
                ';'
            ],
            ' ',
            $text
        );

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

    private function storeDeletedWord(string $text, $deleted): void
    {
        Storage::append(
            'words_deleted_by_filter.txt',
            PHP_EOL . "FROM ID: " . $this->model->getFromId() . PHP_EOL .
            "WORD: " . $deleted
        );
    }

    public function getWordsFromText($cleanedText): array
    {
        return explode(' ', $this->getText());
    }
}
