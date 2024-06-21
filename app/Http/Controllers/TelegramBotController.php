<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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


    public function setWebhook(Response $response)
    {
        // dd('here');
        // Установить вебхук
        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]
        )->json(); //обязательно json
        dd($http);
    }

    public function sendMessage(Request $request)
    {
        Storage::put('HOOK.txt', "lolwut");
        Storage::put('HOOK.txt', json_encode($request->all()));
        Log::info(json_encode($request->all()));
        // Http::post(
        //     env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
        //     ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => $this->message]
        // )->json();
    }
}
