<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Storage;

class BotErrorNotificationService
{

    public static function send(string $message)
    {
        if (env("ENABLE_BOT_NOTIFICATIONS")) {

            $response = Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                [ //MUST BE CHAT ID AND NOT USER ID
                    "chat_id" => env("CHAT_OWNER_ID"),
                    "text" => $message
                ]
            )->json();

            // dd($response);
            if (!$response["ok"]) {
                log::info("ОТПРАВКА СООБЩЕНИЯ НЕ УДАЛАСЬ. " . PHP_EOL . __CLASS__ . PHP_EOL . __METHOD__ . PHP_EOL .
                    "RESPONSE DESCRIPTION: " . $response["description"]);
            }
        }
    }
}
