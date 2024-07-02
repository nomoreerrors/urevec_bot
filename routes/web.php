<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;
use \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Route::get('/', function () {
//     return view('welcome');
// });



//Exclude csrf protection for debug
Route::withoutMiddleware(env("APP_DEBUG") ? [ValidateCsrfToken::class] : [])->group(function () {

    Route::get('/', [TelegramBotController::class, 'setWebhook']);
    Route::get('/getinfo', [TelegramBotController::class, 'getWebhookInfo']);
    Route::post('setChatPermissions', [TelegramBotController::class, 'switchPermissionsNightLightMode']);
    Route::post('/testbot', [TelegramBotController::class, 'testBot']);
});
