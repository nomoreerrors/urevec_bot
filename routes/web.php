<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/get_updates', [TelegramBotController::class, 'getUpdates']);

//эквивалентно https://shuangyu.ru/urevec_bot/ на хостинге
//Просто переходим по url, чтобы Телеграм записал наш адрес в базу и уведомлял о действиях пользователя
Route::get('/', [TelegramBotController::class, 'setWebHook']);
