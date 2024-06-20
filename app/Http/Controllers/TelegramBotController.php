<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramBotController extends Controller
{
    public function getUpdates()
    {

        $apiUrl = "https://api.telegram.org/bot";
        $token = "7239017745:AAGSwLlNc1MTkanpm0w7vBGegQU_o54ptrw";
        $message = "where is my mind?";
        // $message = urlencode($message);

        $userId = "754429643"; //здесь может быть id тг-чата
        Http::get($apiUrl . $token . "/sendMessage", ["chat_id" => $userId, "text" => $message]);
    }


    public function setWebHook()
    {
        dd('Lolwut"');
    }
}
