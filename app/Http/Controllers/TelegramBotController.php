<?php

namespace App\Http\Controllers;

use App\Classes\CommandBuilder;
use App\Classes\CommandsList;
use App\Classes\ModerationSettings;
use App\Exceptions\BaseTelegramBotException;
use App\Jobs\FailedRequestJob;
use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\TextMessageModel;
use App\Services\BotCommandService;
use App\Services\PrivateChatCommandService;
use App\Services\TelegramMiddlewareService;
use GuzzleHttp\Client;
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
    private $requestModel;

    private TelegramBotService $botService;

    public function __construct()
    {
        $this->requestModel = app("requestModel");
        $this->botService = app("botService");
    }
    /**
     * Summary of webhookHandler
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function webhookHandler(Request $request)
    {
        try {
            $this->privateChatCommandHandler();

        } catch (BaseTelegramBotException $e) {
            FailedRequestJob::dispatch($this->requestModel->getData());
            return response(Response::$statusTexts[500], Response::HTTP_OK);
        }

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

    // /**
    //  * Summary of checkIfIsCommand
    //  * @param \App\Models\TelegramRequestModelBuilder $requestModel
    //  * @return bool
    //  */
    // private function checkIfIsCommand(): bool
    // {
    //     if (
    //         $this->requestModel instanceof TextMessageModel &&
    //         $this->requestModel->getIsCommand()
    //     ) {
    //         return true;
    //     }
    //     return false;
    // }


    /**
     * Summary of commandHandler
     * @param mixed $requestModel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function privateChatCommandHandler(): void
    {
        if ($this->requestModel->getChatType() !== "private") {
            return;
        }

        (new PrivateChatCommandService());
    }
}