<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });



Route::get('/', [TelegramBotController::class, 'setWebhook']);
Route::get('/getinfo', [TelegramBotController::class, 'getWebhookInfo']);

// Route::get('/webhook', function(){
//     dd('lohiblya');
// });
Route::get('/webhook', [TelegramBotController::class, 'sendMessage']);