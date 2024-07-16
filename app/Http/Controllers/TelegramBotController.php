<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CONSTANTS;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function webhookHandler(Request $request)
    {
        return response(CONSTANTS::DEFAULT_RESPONSE, Response::HTTP_OK);
    }

    public function getWebhookInfo()
    {
        WebhookService::getInfo();
    }

    public function setWebhook()
    {
        WebhookService::setWebhook();
    }

}
