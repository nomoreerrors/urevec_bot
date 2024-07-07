<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

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
        // dd($request->ip());
        $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
        foreach ($allowedIps as $ip) {
            if ($request->ip() === $ip) {

                return $next($request);
            }
        }
        log::info("ЗАПРОС К СЕРВЕРУ С НЕИЗВЕСТНОГО IP: " . $request->ip());
        return response(Response::$statusTexts[403], Response::HTTP_FORBIDDEN);
    }
}
