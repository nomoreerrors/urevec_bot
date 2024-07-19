<?php

namespace App\Services;

use App\Exceptions\TelegramModelException;
use Illuminate\Http\Request;
use App\Classes\ReplyKeyboardMarkup;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class BotCommandService
{
    /**
     * Class constructor.
     */
    public function __construct(
        private string $command,
        private int $fromId
    ) {
        $this->determineBotCommand();
    }


    public function determineBotCommand()
    {
        if ($this->command === "/moderation_settings") {
            $this->moderationSettingsHandler();

        } else {
            // $botService->sendMessage("Неизвестная команда");
            return response(CONSTANTS::UNKNOWN_CMD, Response::HTTP_OK);
        }

    }

    private function moderationSettingsHandler(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton("Hello!")
            ->get();

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            [
                "chat_id" => $this->fromId,
                "text" => "Hello!",
                "reply_markup" => $keyBoard

            ]
        )->json();
    }
}
