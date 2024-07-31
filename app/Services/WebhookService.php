<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseMediaModel;
use App\Models\TelegramRequestModelBuilder;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\StatusUpdateModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\TextMessageModel;


class WebhookService
{
    public static function setWebhook(): void
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
        // dd($http);
        // dd(get_called_class());
    }

    /**
     * Get webhook info
     * @return void
     */
    public static function getInfo(): void
    {
        $http = Http::get(env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/getWebhookInfo")
            ->json();
        dd($http);
    }
}
