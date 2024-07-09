<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TelegramBotController;
use App\Exceptions\TelegramModelError;
use App\Exceptions\UnexpectedRequestMessage;
use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestSize\Unknown;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\BaseTelegramRequestModel;
use App\Models\UnknownObjectModel;
use Illuminate\Support\Facades\Storage;
use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use App\Services\TelegramBotService;
use Error;
use ErrorException;

class TelegramApiMiddleware
{
    private bool $typeIsExpected = false;


    public function saveRawRequestData(array $data)
    {
        $requestLog = Storage::json("rawrequest.json");

        if (!$requestLog) {
            Storage::put("rawrequest.json", json_encode($data));
        } else {
            $requestLog[] = $data;
            Storage::put("rawrequest.json", json_encode($requestLog));
        }
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
            $error = "Переменная TELEGRAM_CHAT_ADMINS_ID не установлена,
             либо переменные .env недоступны. " . PHP_EOL . __CLASS__;

            BotErrorNotificationService::send($error);
            log::info($error);
            return response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data = $request->all();
        $this->saveRawRequestData($data);
        $expectedTypes = ["message", "edited_message", "chat_member", "message_reaction"];

        foreach ($expectedTypes as $key) {
            if (array_key_exists($key, $data)) {
                $this->typeIsExpected = true;
            };
        }

        if (!$this->typeIsExpected) {
            response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
            throw new UnexpectedRequestMessage(CONSTANTS::UNKNOWN_OBJECT_TYPE, data: json_encode($data), method: __METHOD__, called_class: __CLASS__);
        }




        try {
            $message = (new BaseTelegramRequestModel($data))->create();
            $chatId = $message->getChatId();
            $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
            $allowedChats = explode(",", env("ALLOWED_CHATS_ID"));


            if (!in_array($chatId, $allowedChats)) {
                $error = CONSTANTS::REQUEST_FROM_UNKNOWN_CHAT_ID
                    . PHP_EOL . "CHAT_ID: " . $message->getChatId()
                    . PHP_EOL . __CLASS__;;

                log::info($error);
                BotErrorNotificationService::send($error);
                return response(Response::$statusTexts[403], Response::HTTP_FORBIDDEN);
            }



            if (!in_array($request->ip(), $allowedIps)) {


                $error = CONSTANTS::REQUEST_FROM_UNKNOWN_IP . $request->ip() . PHP_EOL . __CLASS__;

                log::info($error);
                BotErrorNotificationService::send($error);
                // return response(Response::$statusTexts[403], Response::HTTP_FORBIDDEN);
                //ВРЕМЕННО ОТКЛЮЧИЛ ОТВЕТ, ЧТОБЫ ПРОВЕРИТЬ ДИНАМИЧЕСКИЙ IP TELEGRAM


            }
        } catch (TelegramModelError $e) {

            Log::error($e->getInfo() . $e->getData());
            return response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $next($request);
    }
}
