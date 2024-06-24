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

// return response('ok', 200);


        $isAdmin = $service->checkIfUserIsAdmin();
        $isChatMessage = $service->checkIsMessageFromChat();

        if(!$isChatMessage) {
            
            return response('ok', 200); //пришло не сообщение из чата, а уведомление от ТГ
        }
        
        if(!$isAdmin) {
            
            $service->linksFilter($data);
        } else {
            return response('ok', 200); //Сообщение от админа — проверку не проводим
        }





    }


    public function getWebhookInfo()
    {
       $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
                            ->json(); //Обязательно json
       dd($http);
    // Storage::put("NEWHOOK.txt", json_encode($http));
    }
}
