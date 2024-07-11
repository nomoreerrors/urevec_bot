<?php

namespace App\Http\Middleware;

use App\Exceptions\EnvironmentVariablesException;
use App\Http\Controllers\TelegramBotController;
use App\Exceptions\TelegramModelException;
use App\Exceptions\UnexpectedRequestException;
use Closure;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestSize\Unknown;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\BaseTelegramRequestModel;
use App\Exceptions\UnknownChatException;
use App\Exceptions\UnknownIpAddressException;
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
        date_default_timezone_set('Europe/Moscow');
        $requestLog = Storage::json("rawrequest.json");
        $info = $data;
        $info["Moscow_time"] = date("F j, Y, g:i a");
        if (!$requestLog) {
            Storage::put("rawrequest.json", json_encode($info, JSON_UNESCAPED_UNICODE));
        } else {
            $requestLog[] = $info;
            Storage::put("rawrequest.json", json_encode($requestLog, JSON_UNESCAPED_UNICODE));
        }
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->all();
        try {
                if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
                    //не приходит уведомление через exception sendler, т.к. он использует файл .env
                    throw new EnvironmentVariablesException(CONSTANTS::EMPTY_ENVIRONMENT_VARIABLES, __METHOD__);
                }

                $this->saveRawRequestData($data);
                $expectedTypes = ["message", "edited_message", "chat_member", "message_reaction"];


                foreach ($expectedTypes as $key) {
                    if (array_key_exists($key, $data)) {
                        $this->typeIsExpected = true;
                    };
                }
                

                if (!$this->typeIsExpected) {
                    throw new UnexpectedRequestException(CONSTANTS::UNKNOWN_OBJECT_TYPE, __METHOD__);
                }


                $message = (new BaseTelegramRequestModel($data))->create();
                $chatId = $message->getChatId();
                $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
                $allowedChats = explode(",", env("ALLOWED_CHATS_ID"));


                if (!in_array($chatId, $allowedChats)) {
                    throw new UnknownChatException(CONSTANTS::REQUEST_CHAT_ID_NOT_ALLOWED, __METHOD__);
                }


                // if (!in_array($request->ip(), $allowedIps)) {
                //     throw new UnknownIpAddressException(CONSTANTS::REQUEST_IP_NOT_ALLOWED, __METHOD__);
                // }


        } catch (TelegramModelException $e) {
            Log::error($e->getInfo() . $e->getData());
            return response(Response::$statusTexts[500], Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (UnknownChatException | UnknownIpAddressException $e) {

            Log::error($e->getInfo() . $e->getData());
            //ЧТО ДЕЛАТЬ С НЕОБРАБОТАННЫМ ОБЪЕКТОМ, КОТОРЫЙ БОЛЬШЕ НЕ ПРИДЕТ?
            return response(Response::$statusTexts[200], Response::HTTP_OK);
        }


        return $next($request);
    }
}
