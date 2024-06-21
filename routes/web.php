<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/get_updates', [TelegramBotController::class, 'getUpdates']);

//эквивалентно bot_urevec url на хостинге
Route::get('/', [TelegramBotController::class, 'setWebHook']);
