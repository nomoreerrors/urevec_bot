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

    private $data;

    


    public function requestLog(array $data)
    {
        $this->data = $data;

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
         $hasLink = strpos($data["message"]["text"], "http");

    //    dd("внутри фильтра"); 

        if($hasLink !== false) { 

            Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
                    [
                        "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
                        "message_id" => $data["message"]["message_id"]
                     ]
            )->json();
            
            Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
                    [
                        "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
                        "message_id" => $data["message"]["from"]["id"],
                        "untill_date" => time() + 86400
                    ]
            )->json();

            
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




        public function checkIfUserIsAdmin(): bool
        {
            // $adminsIdArray = ["424525424"]; //не админ для теста
            $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
            if(in_array($this->data["message"]["from"]["id"], $adminsIdArray)) {
                return true;

            } else return false;

         
        }


        public function checkIsMessageFromChat(): bool
        {
            // dd($this->data);
        //    dd($this->data["message"]["text"]);
            if(!array_key_exists("text", $this->data["message"])) {
                return false;
               
            }  else return true;
         
}
}