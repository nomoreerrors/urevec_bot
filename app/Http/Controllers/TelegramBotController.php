<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class TelegramBotController extends Controller
{

    private $message = "Supergirl is here!";


    public function getUpdates()
    {
//trump

        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => $this->message]
        );
    }


    public function setWebhook(Response $response)
    {
        // dd('hereee');
        // Установить вебхук
        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]
        )->json(); //обязательно json
        dd($http);
    }

    public function sendMessage(Request $request)
    {
        // Сохранить ответ сервера в файл.
        $data = $request->all();
        Storage::put("DONEee.json", json_encode($data));


        $result = strpos($data["message"]["text"], "http");
dd($result);
        if(strpos()){

            Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => "Пошел нахуй!"]
            )->json(); 
            } else {

            Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => $data["message"]["text"]]
            )->json();

        }



        //Отправить тестовое смс в ответ на любое сообщение в ТГ
        Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => $data["message"]["text"]]
        )->json();
    }


    public function getWebhookInfo()
    {
       $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
                            ->json(); //Обязательно json
       dd($http);
    // Storage::put("NEWHOOK.txt", json_encode($http));
    }
}
