<?php

namespace App\Http\Controllers;

use App\Models\BaseTelegramRequestModel;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\TextMessageModel;
use App\Services\FilterService;
use App\Services\ManageChatSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramBotService;

class TelegramBotController extends Controller
{

    // public const CRON_TOKEN = env("CRON_TOKEN");


    public function setWebhook()
    {

        $http = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setWebhook",
            [
                "url" => env("TELEGRAM_API_WEBHOOK_URL"),
                "allowed_updates" => [
                    "chat_member",
                    "message",
                    "edited_message",
                    "message_reaction",
                    "message_reaction_count"
                ]
            ]
        )->json(); //обязательно json
        dd($http);
    }


    public function switchPermissionsNightLightMode(Request $request)
    {
        $data = $request->all();

        Storage::append("cron_requests.txt", json_encode($data));

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
                    return response('ok', Response::HTTP_OK, ['mode' => 'night_mode']);
                }
            }


            if ($data["mode"] === "light_mode") {
                $result = $chatPermissions->setPermissionsToLightMode();

                if ($result) {
                    log::info("Set to light mode time . " . time());
                    return response('ok', Response::HTTP_OK,  ['mode' => 'light_mode']);
                }
            }
        }
        log::error("Failed to switch night/light permissions mode");
        return response("Failed switch to night/light permissions mode", Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    public function webhookHandler(Request $request)
    {
        $data = $request->all();


        $message = (new BaseTelegramRequestModel($data))->create();

        $service = new TelegramBotService($message);
        $service->saveRawRequestData($data);
        $service->requestLog();



        if ($service->blockNewVisitor()) {
            return response('new member blocked for 24 hours', Response::HTTP_OK);
        };

        if ($service->blockUserIfMessageIsForward()) {
            return response('user blocked', Response::HTTP_OK);
        }

        if ($service->blockUserIfMessageHasLink()) {
            return response('user blocked', Response::HTTP_OK);
        }

        if ($service->deleteMessageIfContainsBlackListWords()) {
            return response("Message deleted by filter", Response::HTTP_OK);
        }



        return response('default response', Response::HTTP_OK);
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
