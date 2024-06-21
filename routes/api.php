<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//Не забывай про префикс api у каждого роута
//эквивалентно https://shuangyu.ru/urevec_bot/ на хостинге
//Просто переходим по url, чтобы Телеграм записал наш адрес в базу и уведомлял о действиях пользователя
//Для хуков всегда нужно использовать api.php, чтобы Ларавел не запрашивал токены безопасности
// Route::post('/', [TelegramBotController::class, 'setWebhook']);
