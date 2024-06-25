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

    private $messageType = "";

    


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
        $hasLink = strpos($this->data[$this->messageType]["text"], "http");
        
         
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
            
            $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
            // if(array_key_exists("message",  $this->data) ||
            // array_key_exists("edited_message", $this->data));
            // dd("checkifuserisadmin " . "this->data->message = " . $this->data["message"] . 
            // "this->data->editedmessage = " . $this->data["edited_message"]);
            if(array_key_exists($this->messageType, $this->data)) {

                if(in_array($this->data[$this->messageType]["from"]["id"], $adminsIdArray)) {
                $result = true;
                Log::info("isAdmin return true");
                

            } else {
                $result;
                Log::info("isAdmin return false");
                $result = false;
            }
            return $result;
            }
           
        }


        public function checkIsMessageFromChat(): bool
        {
            $this->messageType = "";

            if(array_key_exists("message", $this->data)) {
                $this->messageType = "message";

            } elseif(array_key_exists("edited_message", $this->data)) {
                 $this->messageType = "edited_message";
            } 
            else {
                return false;
            } 
            
            // if(array_key_exists("text", $this->data["message"])) {
            // Log::info("Поле текст объекта data :" . $this->data["message"]["text"]);  
            // $result = array_key_exists("text", $this->data["message"]);
            // Log::info("поле текст существует: " . $result);
            // } else {
            //     Log::info("Поля ТЕКСТ не существует в объекте");
            // }

            if(!array_key_exists("text", $this->data[$this->messageType])) {
                
                Log::info("checkIsMessageFromChat returned false");
                return false;
               
            }  else {
                
                Log::info("checkIsMessageFromChat returned true");
                return true;
            } 
         
}
}