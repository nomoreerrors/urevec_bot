<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });



Route::get('/', [TelegramBotController::class, 'setWebhook']);
