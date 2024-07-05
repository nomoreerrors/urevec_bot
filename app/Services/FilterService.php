<?php

namespace App\Services;

use App\Models\BaseTelegramRequestModel;
use App\Models\TelegramMessageModel;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class FilterService extends BaseService
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
        if (empty($badWords)) {
            throw new Exception("Отстутствует файл фильтра сообщений storage/app/badwords.json");
        }

        if (empty($this->message->getText())) {
            return false;
        }


        foreach ($badWords as $word) {
            if (str_contains($this->message->getText(), mb_strtolower($word))) {
                log::info($word);
                return true;
            }
        }

        return false;
    }
}
