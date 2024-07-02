<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class FilterService extends BaseService
{

    // /**
    //  * Фильтр слов для удаления мата и рекламы
    //  * @throws \Exception
    //  * @return bool
    //  */
    // public function wordsFilter(): bool
    // {
    //     $badWords = json_decode(Storage::get('badwords.json'), true);
    //     if ($badWords === null) {
    //         throw new Exception("Отстутствует файл фильтра сообщений storage/app/badwords.json");
    //     }


    //     if (!array_key_exists("message", $this->data) && !array_key_exists("edited_message", $this->data)) {
    //         return false;
    //     }



    //     if (array_key_exists("text", $this->data[$this->messageType])) {

    //         $text = $this->data[$this->messageType]["text"];

    //         foreach ($badWords as $word) {
    //             if (str_contains($text, $word)) {

    //                 return true;
    //             }
    //         }
    //     }
    //     return false;
    // }



    // /**
    //  * Поиск ссылок в text value и проверка на наличие текстовых ссылок
    //  * @return bool
    //  */
    // public function linksFilter(): bool
    // {

    //     if (!$this->text) {
    //         return false;
    //     }

    //     $hasLink = str_contains($this->text, "http");

    //     if ($hasLink || $this->textLink) {
    //         return true;
    //     }
    //     return false;
    // }
}
