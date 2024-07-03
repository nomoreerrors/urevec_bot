<?php

namespace App\Http\Controllers;

use App\Models\MessageModel;
use App\Models\TelegramMessageModel;
use App\Services\ManageChatSettingsService;
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


    public function switchPermissionsNightLightMode(Request $request)
    {
        $data = $request->all();

        $chatPermissions = new ManageChatSettingsService();

        $cronToken = array_key_exists("token", $data) ? $data["token"] : null;
        if ($cronToken !== env("CRON_TOKEN")) {
            return response("Неверный токен запроса", 400);
        }


        if (array_key_exists("mode", $data)) {
            if ($data["mode"] === "night_mode") {

                $result = $chatPermissions->setPermissionsToNightMode();
                if ($result) {
                    log::info("Set to night mode time :" . time());
                    return response('ok', 200, ['mode' => 'night_mode']);
                }
            }
            if ($data["mode"] === "light_mode") {
                $result = $chatPermissions->setPermissionsToLightMode();

                if ($result) {
                    log::info("Set to light mode time . " . time());
                    return response('ok', 200,  ['mode' => 'light_mode']);
                }
            }
        }
        log::error("Failed to switch night/light permissions mode");
        return response("Failed switch to night/light permissions mode", 200);
    }



    public function webhookHandler(Request $request)
    {

        if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
            throw new \Exception("Переменная TELEGRAM_CHAT_ADMINS_ID не установлена, либо переменные .env недоступны");
        }

        $data = $request->all();
        $message = new TelegramMessageModel($data);
        //request log всего объекта положим в middleware
        $service = new TelegramBotService($message);
        $service->requestLog();



        if (
            $message->getType() !== "message" &&
            $message->getType() !== "edited_message" &&
            $message->getType() !== "chat_member"
        ) {
            log::info($message->getType(), $data);
            return response('unknown message type', 200);
        }




        if (!$message->getFromAdmin()) {

            if ($message->getIsNewMemberJoinUpdate()) {
                $service->blockNewVisitor();

                return response('new member blocked for 24 hours', 200);
            }



            if ($message->getHasLink() || $message->getIsForwardMessage()) {
                $isBlocked = $service->banUser();
                if ($isBlocked) {
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
        $service->sendMessage("scream 2");
    }
}
