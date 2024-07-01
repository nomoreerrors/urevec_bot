<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Не забывай про префикс api у каждого роута
//эквивалентно https://shuangyu.ru/urevec_bot/api/webhook на хостинге
Route::post('/webhook', [TelegramBotController::class, 'webhookHandler']);
Route::post('/testbot', [TelegramBotController::class, 'testBot']);
Route::post('setChatPermissions', [TelegramBotController::class, 'setChatPermissions']);
