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


    public function setWebHook(Response $response)
    {
        // dd(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook");
        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]

        );

        $data = $response->getStatusCode();
        dd($data);
        // dd('Lolwut"');
    }
}
