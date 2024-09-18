<?php

namespace App\Http\Controllers;

use App\Classes\CommandBuilder;
use App\Services\BotErrorNotificationService;
use App\Services\ChatRulesService;
use App\Classes\CommandsList;
use App\Classes\ChatRules;
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


    public function webhookHandler()
    {
        switch ($this->requestModel->getChatType()) {
            case "private":
                $this->botService->commandHandler()->handle();
                break;
            case "group":
            case "supergroup":
                $this->validateChatRules();
                break;
        }

        return response(CONSTANTS::DEFAULT_RESPONSE, Response::HTTP_OK);
    }

    public function validateChatRules()
    {
        (new ChatRules($this->botService, new ChatRulesService($this->botService)))->validate();
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