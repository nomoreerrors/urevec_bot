<?php

namespace App\Http\Controllers;

use App\Models\BaseTelegramRequestModel;
use App\Models\TextMessageModel;
use App\Services\BotCommandService;
use Illuminate\Support\Facades\Cache;
use App\Classes\ReplyKeyboardMarkup;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramBotService;
use App\Services\WebhookService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Services\CONSTANTS;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    /**
     * Summary of webhookHandler
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function webhookHandler(Request $request)
    {
        $model = app("requestModel");
        app()->instance("botService", new TelegramBotService($model));

        $this->commandHandler($model);

        return response(CONSTANTS::DEFAULT_RESPONSE, Response::HTTP_OK);
    }

    /**
     * Summary of getWebhookInfo
     * @return void
     */
    public function getWebhookInfo()
    {
        WebhookService::getInfo();
    }

    /**
     * Summary of setWebhook
     * @return void
     */
    public function setWebhook()
    {
        WebhookService::setWebhook();
    }

    /**
     * Summary of checkIfIsCommand
     * @param \App\Models\BaseTelegramRequestModel $model
     * @return bool
     */
    private function checkIfIsCommand(BaseTelegramRequestModel $model)
    {
        if (
            $model->getFromAdmin() &&
            $model instanceof TextMessageModel &&
            $model->getIsCommand()
        ) {
            Log::info("inside the checkIfIsCommand");
            return true;
        }
        Log::info("inside the checkIfIsCommand false");
        return false;
    }

    /**
     * Summary of commandHandler
     * @param mixed $model
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function commandHandler($model): void
    {
        if ($this->checkIfIsCommand($model)) {
            $command = $model->getText();
            $chatId = $model->getFromId();
            $commandService = new BotCommandService($command, $chatId);
        }
    }

    /**
     * Set bot menu button and commands list
     * @return void
     */
    public function setMyCommands(): void
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/setMyCommands",
            [
                "commands" => [
                    ["command" => "moderation_settings", "description" => "Настроить модерацию чата"],
                    // ["command" => "getmenu2", "description" => "get menu2"],
                    // ["command" => "getmenu3", "description" => "get menu2"],

                ]
            ]
        )->json();
        // dd($response);
    }

}
