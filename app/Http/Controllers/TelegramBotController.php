<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramBotService;
use ErrorException;

class TelegramBotController extends Controller
{


    public function setWebhook()
    {

        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            [
                "url" => env("TELEGRAM_API_WEBHOOK_URL"),
                "allowed_updates" => ["chat_member", "message", "edited_message"]
            ]
        )->json(); //обязательно json

    }



    public function webhookHandler(Request $request, TelegramBotService $service)
    {
        if (env("TELEGRAM_CHAT_ADMINS_ID") === "") {
            throw new \Exception("Переменная TELEGRAM_CHAT_ADMINS_ID не установлена, либо переменные .env недоступны");
        }

        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        //По какой логине забанена Ксения Киселева? Пригласила чувака и в бан. Объект "145"
        $data = $request->all();

        $service->requestLog($data);
        $messageType = $service->checkMessageType();
        $hasLink = $service->linksFilter();

        if ($messageType !== "message" && $messageType !== "edited_message" && $messageType !== "chat_member") {
            log::info($messageType, $data);
            return response('unknown message type', 200);
        }

        $isAdmin = $service->checkIfUserIsAdmin();
        if (!$isAdmin) {

            $isNewUser = $service->blockNewVisitor();
            if ($isNewUser) {
                return response('new member blocked for 24 hours', 200);
            }


            if ($hasLink !== false) { //ссылка есть

                $isBlocked = $service->banUser();
                if ($isBlocked) {

                    // log::info($data);
                    return response('user blocked', 200);
                }
            }
        }
        return response('default response', 200);
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
    }
}
