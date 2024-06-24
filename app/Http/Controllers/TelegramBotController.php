<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramBotService;

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
        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]
        )->json(); //обязательно json
        dd($http);
    }

    public function sendMessage(Request $request, TelegramBotService $service)
    {
        
        $data = $request->all();
        $service->requestLog($data);

        if(!property_exists((object)$data["message"], "text")) {
        return response('ok', 200);
        };

        $service->linksFilter($data);

// dd('Lolwut');
       

//         $result = strpos($data["message"]["text"], "http");
        
//         if($result !== false){
// //замени id на настоящий Юрьевец
//             Http::post( 
//                 env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
//                 ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => "Пошел нахуй!"]
//             )->json(); 
//             return;
//             } else {

//             Http::post(
//                 env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
//                 ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => $data["message"]["text"] . "message doesn't contain http"]
//             )->json();
//             return;

//         }


    }


    public function getWebhookInfo()
    {
       $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
                            ->json(); //Обязательно json
       dd($http);
    // Storage::put("NEWHOOK.txt", json_encode($http));
    }
}
