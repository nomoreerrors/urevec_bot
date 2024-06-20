<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', [TelegramBotController::class, 'getUpdates']);
// Route::patch('posts/{id}/restore', [BlogPostController::class, 'restore'])
//     ->name('blog.admin.posts.restore');
Route::get('/hook', [TelegramBotController::class, 'setWebHook']);
