<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramBotController extends Controller
{

    private $message = "where is my mind?";


    public function getUpdates()
    {



        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => $this->message]
        );
    }


    public function setWebHook(Request $request)
    {
        //Установить вебхук
        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]

        );

        dd($request);

        //Отправить тестовое сообщение в тг в ответ на успешную регистрацию хука
        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => $this->message]
        );
        // dd($data);
        // dd('Lolwut"');
    }
}
