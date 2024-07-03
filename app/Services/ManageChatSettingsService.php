<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ManageChatSettingsService extends BaseService
{

    public function setPermissionsToNightMode(): bool
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
        )->json(); //обязательно json

        if ($response["ok"] === true) {
            return true;
        } else
            return false;
    }



    public function setPermissionsToLightMode(): bool
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
        )->json(); //обязательно json

        if ($response["ok"] === true) {
            return true;
        } else
            return false;
    }
}
