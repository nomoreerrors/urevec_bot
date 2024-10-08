<?php

// use App\Http\Middleware\ChatRulesMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;
use App\Http\Middleware\TelegramApiMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Не забывай про префикс api у каждого роута
//эквивалентно https://shuangyu.ru/urevec_bot/api/webhook на хостинге
Route::post('/webhook', [TelegramBotController::class, 'webhookHandler'])
    ->middleware([
        TelegramApiMiddleware::class,
        // ChatRulesMiddleware::class
    ]);


