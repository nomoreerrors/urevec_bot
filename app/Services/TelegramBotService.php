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


    public function linksFilter()
    {
         $hasLink = strpos($this->data["message"]["text"], "http");

Log::info("Внутри фильтра ссылок");
        if($hasLink !== false) { 

           
            
            Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
                    [
                        "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
                        "user_id" => $this->data["message"]["from"]["id"],
                        "can_send_messages" => false,
                        "can_send_documents" => false,
                        "can_send_photos" => false,
                        "can_send_videos" => false,
                        "can_send_video_notes" => false,
                        "can_send_other_messages" => false,
                        "until_date" => time() + 86400
                    ]
            )->json();
Log::info("deleteMessage");
             Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
                    [
                        "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
                        "message_id" => $this->data["message"]["message_id"]
                     ]
            )->json();
           Log::info("sendMesssage");
            Http::post( 
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
                [
                    "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
                    "text" => "Пользователь " . $this->data["message"]["from"]["first_name"] . " заблокирован на 24 часа за нарушение правил чата."
                ]
            )->json(); 
            return;

        } 
        // else {

        //     Http::post( //Это только для теста. Потом удали
        //         env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
        //         ["chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"), "text" => $this->data["message"]["text"] . " message doesn't contain http "]
        //     )->json();
        //     return;
        // }

    }




        public function checkIfUserIsAdmin(): bool
        {
            // $adminsIdArray = ["424525424"]; //не админ для теста
            $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
            if(in_array($this->data["message"]["from"]["id"], $adminsIdArray)) {

                Log::info("isAdmin return true");
                return true;

            } else {
                Log::info("isAdmin return false");
                return false;
            }
         
        }


        public function checkIsMessageFromChat(): bool
        {

            if(array_key_exists("text", $this->data["message"])) {
            Log::info("Поле текст объекта data :" . $this->data["message"]["text"]);  
            $result = array_key_exists("text", $this->data["message"]);
            Log::info("поле текст существует: " . $result);
            } else {
                Log::info("Поля ТЕКСТ не существует в объекте");
            }

            if(!array_key_exists("text", $this->data["message"])) {
                Log::info("checkIsMessageFromChat returned false");
                return false;
               
            }  else {
                
                Log::info("checkIsMessageFromChat returned true");
                return true;
            } 
         
}
}