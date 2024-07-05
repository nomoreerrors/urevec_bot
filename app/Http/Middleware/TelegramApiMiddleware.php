<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $next($request);
    }
}
