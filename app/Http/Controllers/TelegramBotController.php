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
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]
        )->json(); //обязательно json
        // dd($http);
    }



    public function webhookHandler(Request $request, TelegramBotService $service)
    {
        //tartarariiiii
        $data = $request->all();

        $service->requestLog($data);
        $messageType = $service->checkMessageType();
        $isAdmin = $service->checkIfUserIsAdmin();

        if ($messageType !== "message" && $messageType !== "edited_message") {
            log::info($messageType, $data);
            return response('unknown message type', 200);
        }

        if (!$isAdmin) {
            try {

                $isNewUser = $service->blockNewVisitor();

                if ($isNewUser) {
                    return response('new member blocked for 24 hours', 200);
                }
                $hasLink = $service->linksFilter();

                if ($hasLink !== false) { //ссылка есть

                    $isBlocked = $service->banUser();
                    if ($isBlocked) {

                        // log::info($data);
                        return response('user blocked', 200);
                    }
                }
            } catch (ErrorException $e) {
                Log::error($e->getMessage());
            };
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
