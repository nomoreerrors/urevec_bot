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


    public function getUpdates()
    {
        //trump

        Http::get(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            ["chat_id" => env('TELEGRAM_API_TEST_USER_ID'), "text" => "Hello world"]
        );
    }


    public function setWebhook()
    {

        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            ["url" => env("TELEGRAM_API_WEBHOOK_URL")]
        )->json(); //обязательно json
        // dd($http);
    }



    public function sendMessage(Request $request, TelegramBotService $service)
    {
        $data = $request->all();

        $service->requestLog($data);
        $messageType = $service->checkMessageType();
        $isAdmin = $service->checkIfUserIsAdmin();

        if ($messageType !== "message" && $messageType !== "edited_message") {
            return response('ok', 200);
        }

        if (!$isAdmin) {
            try {
                $hasLink = $service->linksFilter();

                if ($hasLink) { //ссылка есть

                    $service->restrictUser(time() + 86400);
                    $$service->deleteMessage();
                    $service->sendMessage("Пользователь " . $service->data[$service->messageType]["from"]["first_name"] . " заблокирован на 24 часа за нарушение правил чата.");
                    //Позже создать объект с готовыми сообщениями
                }
            } catch (ErrorException $e) {
                Log::error($e->getMessage());
            };
        } else {
            return response('ok', 200);
        }
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
        $array = ["must" => "lolwut", "have" => "detonator"];
        $bot = "have";
        if ($bot !== "lol" && $bot !== "have") {
            dd("true");
        }

        $testObjects = json_decode(file_get_contents("/Users/nomoreerrors/Documents/sftptest/tests/Unit/TestObjects.json"), true);
        // dd($testObjects["0"]);
        foreach ($testObjects as $object) {
            $messageType = "";
            if (
                array_key_exists("message", $object) ||
                array_key_exists("edited_message", $object)
            ) {
                $messageType = $object;
                dd($messageType);
            }
        }
    }
}
