<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class TelegramBotService
{

    public function requestLog(array $data)
    {
          $requestLog = Storage::json("DONE.json");

        if(!$requestLog){
            Storage::put("DONE.json", json_encode($data)); 
        } else {
            $requestLog[] = $data;
            Storage::put("DONE.json", json_encode($requestLog));
        }
    }


    public function linksFilter(array $data)
    {
         $result = strpos($data["message"]["text"], "http");
        
        if($result !== false){
//замени id на настоящий Юрьевец
            Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => "Пошел нахуй!"]
            )->json(); 
            return;
            } else {

            Http::post( //Это только для теста. Потом удали
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => $data["message"]["text"] . "message doesn't contain http"]
            )->json();
            return;
    }

}
}