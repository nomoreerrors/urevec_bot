<?php

use App\Exceptions\BaseTelegramBotException;
use App\Services\BotErrorNotificationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\FailedRequestJob;
use Illuminate\Support\Facades\Request;

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

            Log::error($e);

            if (!($e instanceof BaseTelegramBotException)) {
                BotErrorNotificationService::send($e->getMessage() . PHP_EOL . "Class: " . $e->getFile() . " Line: " . $e->getLine());
            }

            FailedRequestJob::dispatch(request()->all());

            return response($e->getMessage(), 200);
        });
    })->create();
