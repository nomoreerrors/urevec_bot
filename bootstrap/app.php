<?php

use App\Exceptions\TelegramModelException;
use App\Services\BotErrorNotificationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {

            if (!($e instanceof TelegramModelException)) {
                BotErrorNotificationService::send(get_class($e) . PHP_EOL . $e->getMessage() . PHP_EOL . "Class: " . $e->getFile());
            }
        });
    })->create();
