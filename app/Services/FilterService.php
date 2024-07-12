<?php

namespace App\Services;

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


    private BaseTelegramRequestModel $message;


    public function __construct(BaseTelegramRequestModel $message)
    {
        $this->message = $message;
    }

    /**
     * Фильтр слов для удаления мата и рекламы
     * @throws \Exception
     * @return bool
     */
    public function wordsFilter(): bool
    {
        if (empty($this->message->getText())) {
            return false;
        }
        $badWords = json_decode(Storage::get('badwords.json'), true);
        $phrases = json_decode(Storage::get('badPhrases.json'), true);

        if (empty($badWords) || empty($phrases)) {
            response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);


            throw new Exception("Отстутствует файл фильтра сообщений storage/app/badwords.json или badPhrases.json");
        }

        if (empty($this->message->getText())) {
            return false;
        }



        $string = str_replace(
            [
                '.', ',', '!', '?', '&',
                '/', '"', '(', ')', ';'
            ],
            " ",
            $this->message->getText()
        );

        $string = mb_strtolower($string);

        foreach ($phrases as $phrase) {
            if (str_contains($string, $phrase)) {
                Storage::append(
                    "words_deleted_by_filter.txt",
                    PHP_EOL . "FROM ID: " . $this->message->getFromId() . PHP_EOL .
                        "WORD: " . $phrase . PHP_EOL . "FROM TEXT: " .
                        $this->message->getText() . PHP_EOL
                );
                return true;
            }
        }


        $arrayFromString = explode(" ", $string);

        foreach ($arrayFromString as $key => $value) {

            if (strlen($value) <= 6) { //Unicode занимает больше битов
                unset($arrayFromString[$key]);
            }
        }

        foreach ($arrayFromString as $key => $value) {

            if (in_array($value, $badWords)) {
                Storage::append(
                    "words_deleted_by_filter.txt",
                    PHP_EOL . "FROM ID: " . $this->message->getFromId() . PHP_EOL .
                        "WORD: " . $value . PHP_EOL . "FROM TEXT: " .
                        $this->message->getText() . PHP_EOL
                );
                return true;
            }
        }



        return false;
    }
}
