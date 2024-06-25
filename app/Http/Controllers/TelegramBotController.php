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


    public function getUpdates()
    {
        //trump

        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => "Hello world"]
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
        Log::info("Вошел");

        $isChatMessage = $service->checkIsMessageFromChat();
        $isAdmin = $service->checkIfUserIsAdmin();

        if (!$isChatMessage) {
            Log::info("Не сообщение из чата. Вероятно, уведомление о новом пользователе");
            return response('ok', 200);
        }

        // if (!$isAdmin) {
        //     Log::info("Не администратор");
        //     $service->linksFilter();
        // } else {
        //     Log::info("Сообщение отправил администратор");
        //     return response('ok', 200);
        // }
        Log::info("Не администратор");
        $service->linksFilter();
        return response('ok', 200);
    }


    public function getWebhookInfo()
    {
        $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
            ->json(); //Обязательно json
        dd($http);
        // Storage::put("NEWHOOK.txt", json_encode($http));
    }



    public function testBot(Request $request, TelegramBotService $service): void
    {

        // $result =  json_decode(Storage::get('TestObjects.json'), true);
        // dd($result);
        // // dd(Storage::get("TestObjects.json"));
        // $testObjects = Storage::get(json_decode("TestObjects.json", true));
        // dd($testObjects);

        // dd($service->data);
        // dd("lol");
        // $data = $request->all();
        //       Http::post( 
        //         env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
        //             [
        //                 "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
        //                 "user_id" => $data["message"]["from"]["id"],
        //                 "can_send_messages" => false,
        //                 "can_send_documents" => false,
        //                 "can_send_photos" => false,
        //                 "can_send_videos" => false,
        //                 "can_send_video_notes" => false,
        //                 "can_send_other_messages" => false,
        //                 "until_date" => time() + 86400
        //             ]
        //     )->json();

        //       Http::post( 
        //         env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
        //             [
        //                 "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
        //                 "message_id" =>$data["message"]["message_id"]
        //              ]
        //     )->json();

        //        Http::post( 
        //         env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
        //         [
        //             "chat_id" => env("TELEGRAM_CHAT_UREVEC_ID"),
        //             "text" => "Пользователь " . $data["message"]["from"]["first_name"] . " заблокирован на 24 часа за нарушение правил чата."
        //         ]
        //     )->json(); 


    }
}
