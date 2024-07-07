<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TelegramBotController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\BaseTelegramRequestModel;
use App\Services\TelegramBotService;

class TelegramApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(env("TELEGRAM_CHAT_ADMINS_ID"))) {
            throw new \Exception("Переменная TELEGRAM_CHAT_ADMINS_ID не установлена, либо переменные .env недоступны");
        }

        $data = $request->all();
        $message = (new BaseTelegramRequestModel($data))->create();

        $chatId = $message->getChatId();


        // $chatId = (new BaseTelegramRequestModel($data))->create()->getChatId();

        $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
        $allowedChats = explode(",", env("ALLOWED_CHATS_ID"));

        // dd(in_array($chatId, $allowedChats));
        if (!in_array($chatId, $allowedChats)) {
            log::info("ЗАПРОС ИЗ НЕИЗВЕСТНОГО ЧАТА. ID ЧАТА НЕТ В СПИСКЕ РАЗРЕШЕННЫХ ИЛИ СПИСОК В ФАЙЛЕ ENV НЕ УСТАНОВЛЕН " . $request->ip());
            return response(Response::$statusTexts[403], Response::HTTP_FORBIDDEN);
        }


        // dd(in_array($request->ip(), $allowedIps));
        if (!in_array($request->ip(), $allowedIps)) {

            log::info("ЗАПРОС К СЕРВЕРУ С НЕИЗВЕСТНОГО IP ИЛИ ENV-СПИСОК РАЗРЕШЕННЫХ АДРЕСОВ НЕ УСТАНОВЛЕН: " . $request->ip());
            return response(Response::$statusTexts[403], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
