<?php

namespace App\Http\Controllers;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Exceptions\TelegramModelException;
use App\Models\BaseTelegramRequestModel;
use App\Models\ForwardMessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\UnknownObjectModel;
use App\Services\BotErrorNotificationService;
use App\Models\InvitedUserUpdateModel;
use App\Exceptions\TelegramModelError;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MessageModel;
use App\Services\CONSTANTS;
use App\Services\ManageChatSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramBotService;
use ErrorException;


class TelegramBotController extends Controller
{

    // public const CRON_TOKEN = env("CRON_TOKEN");




    public function webhookHandler(Request $request)
    {
        // dd(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));

        $data = $request->all();
        $message = (new BaseTelegramRequestModel($data))->create();
        $service = new TelegramBotService($message);

        // dd(get_class_vars($message));
        $service->prettyRequestLog();


        try {
            if ($service->blockNewVisitor()) {
                return response(CONSTANTS::NEW_MEMBER_RESTRICTED, Response::HTTP_OK);
            }

            if ($service->blockUserIfMessageIsForward()) {
                return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
            }

            if ($service->ifMessageHasLinkBlockUser()) {
                return response(CONSTANTS::MEMBER_BLOCKED, Response::HTTP_OK);
            }

            if ($service->deleteMessageIfContainsBlackListWords()) {
                return response(CONSTANTS::DELETED_BY_FILTER, Response::HTTP_OK);
            }


        } catch (TelegramModelException | RestrictMemberFailedException | BanUserFailedException $e) {
            Log::error($e->getInfo() . $e->getData());
            return response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return response(CONSTANTS::DEFAULT_RESPONSE, Response::HTTP_OK);
    }


    public function getWebhookInfo()
    {
        $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
            ->json();
        dd($http);
    }

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
                    return response('ok', Response::HTTP_OK, ['mode' => 'light_mode']);
                }
            }
        }
        log::error("Failed to switch night/light permissions mode");
        return response("Failed switch to night/light permissions mode", Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    public function testBot(Request $request, TelegramBotService $service): void
    {
        $service->sendMessage("scream 2");
    }
}
