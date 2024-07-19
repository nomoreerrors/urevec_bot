<?php

namespace App\Services;

use App\Exceptions\TelegramModelException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ChatSettingsService
{
    public static function setPermissionsToNightMode(): bool
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setChatPermissions",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "permissions" => [
                    "can_send_messages" => true,
                    "can_send_audios" => false,
                    "can_send_documents" => false,
                    "can_send_photos" => false,
                    "can_send_videos" => false,
                    "can_send_video_notes" => false,
                    "can_send_voice_notes" => false,
                    "can_send_polls" => false,
                    "can_send_other_messages" => false,
                    "can_add_web_page_previews" => false,
                    "can_change_info" => false,
                    "can_invite_users" => true,
                    "can_pin_messages" => false,
                    "can_manage_topics" => false,
                ]
            ]
        )->json();

        if ($response["ok"] === true) {
            return true;
        } else
            return false;
    }



    public static function setPermissionsToLightMode(): bool
    {

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setChatPermissions",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "permissions" => [
                    "can_send_messages" => true,
                    "can_send_audios" => true,
                    "can_send_documents" => false,
                    "can_send_photos" => true,
                    "can_send_videos" => true,
                    "can_send_video_notes" => true,
                    "can_send_voice_notes" => true,
                    "can_send_polls" => false,
                    "can_send_other_messages" => true,
                    "can_add_web_page_previews" => false,
                    "can_change_info" => false,
                    "can_invite_users" => true,
                    "can_pin_messages" => false,
                    "can_manage_topics" => false,
                ]
            ]
        )->json();

        if ($response["ok"] === true) {
            return true;
        } else
            return false;
    }

    /**
     * Switch night light mode
     * @param array $requestData
     * @throws \App\Exceptions\TelegramModelException
     * @return \Illuminate\Http\Response
     */
    public static function setNightLightMode(array $requestData): \Illuminate\Http\Response
    {
        $data = array_merge($requestData, ['Moscow_time' => date("F j, Y, g:i a")]);
        Storage::append("cron_requests.txt", json_encode($data));
        $cronToken = array_key_exists("token", $data) ? $data["token"] : null;

        if ($cronToken !== env("CRON_TOKEN")) {
            response("Неверный токен запроса", 400);
        }

        if (array_key_exists("mode", $data)) {

            if ($data["mode"] === "night_mode") {
                $result = self::setPermissionsToNightMode();
                if ($result) {
                    log::info("Set to night mode time :" . time());
                    return response("OK", Response::HTTP_OK, ['mode' => 'night_mode']);
                }
            }

            if ($data["mode"] === "light_mode") {
                $result = self::setPermissionsToLightMode();
                if ($result) {
                    log::info("Set to light mode time . " . time());
                    return response('ok', Response::HTTP_OK, ['mode' => 'light_mode']);
                }
            }
        }
        log::error("Failed to switch night/light permissions mode");
        response("Failed switch to night/light permissions mode", Response::HTTP_INTERNAL_SERVER_ERROR);
        throw new TelegramModelException("Failed switch to night/light permissions mode", __METHOD__);
    }
}
