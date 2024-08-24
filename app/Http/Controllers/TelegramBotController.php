<?php

namespace App\Http\Controllers;

use App\Classes\CommandBuilder;
use App\Classes\CommandsList;
use App\Classes\Menu;
use App\Classes\ChatSelector;
use App\Classes\ModerationSettings;
use App\Exceptions\BaseTelegramBotException;
use App\Jobs\FailedRequestJob;
use App\Models\TelegramRequestModelBuilder;
use App\Models\MessageModels\TextMessageModel;
use App\Services\BaseBotCommandCore;
use App\Classes\PrivateChatCommandCore;
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
        $this->botService = app(TelegramBotService::class);
        $this->requestModel = $this->botService->getRequestModel();
    }


    public function webhookHandler(Request $request)
    {
        switch ($this->requestModel->getChatType()) {
            case "private":
                $this->botService->commandHandler()->handle();
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
}