<?php

namespace App\Http\Controllers;

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
        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => $this->message]
        );

        $data = $request->getContent();
        dd($data);
        // dd('Lolwut"');
    }
}
