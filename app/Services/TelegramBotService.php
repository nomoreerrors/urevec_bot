<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ErrorException;
use Exception;

class TelegramBotService
{

    public $data;

    public string $messageType = "";




    public function requestLog(array $data)
    {
        $this->data = $data;
        $requestLog = Storage::json("DONE.json");

        if (!$requestLog) {
            Storage::put("DONE.json", json_encode($data));
        } else {
            $requestLog[] = $data;
            Storage::put("DONE.json", json_encode($requestLog));
        }
    }


    public function linksFilter(): bool
    {
        log::info("inside links filter");
        if (
            $this->messageType !== "message" &&
            $this->messageType !== "edited_message"
        ) {

            log::info("linksfilter. Не текстовое сообщение и не редактированное");

            return false;
        } elseif (array_key_exists("text", $this->data[$this->messageType])) {
            log::info("Linksfilter: поле текст существует");
            $hasLink = str_contains($this->data[$this->messageType]["text"], "http");

            log::info("hasLink value = " . (string)$hasLink);
            if ($hasLink) {
                log::info("ссылка обнаружена ", $this->data);
                return true;
            }
        }

        return false;
    }




    public function checkIfUserIsAdmin(): bool
    {


        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
        $result = null;
        if (array_key_exists($this->messageType, $this->data)) {
            log::info($adminsIdArray);
            log::info($this->data[$this->messageType]["from"]["id"]);
            if ((string)in_array($this->data[$this->messageType]["from"]["id"], $adminsIdArray)) {

                $result = true;
                Log::info("isAdmin return true" . $this->data[$this->messageType]["from"]["id"]);
            } else {
                //ПОЧЕМУ НЕ ЛОВИТ АДМИН АЙДИ НА СЕРВЕРЕ???
                //ПОЧЕМУ НЕ ЛОВИТ АДМИН АЙДИ НА СЕРВЕРЕ???
                //ПОЧЕМУ НЕ ЛОВИТ АДМИН АЙДИ НА СЕРВЕРЕ???
                //ПОЧЕМУ НЕ ЛОВИТ АДМИН АЙДИ НА СЕРВЕРЕ???
                //ПОЧЕМУ НЕ ЛОВИТ АДМИН АЙДИ НА СЕРВЕРЕ???
                Log::info("isAdmin return false");
                $result = false;
            }
        }

        return $result;
    }


    public function checkMessageType(): string
    {
        $this->messageType = "";

        if (array_key_exists("message", $this->data)) {
            $this->messageType = "message";
        } elseif (array_key_exists("edited_message", $this->data)) {
            $this->messageType = "edited_message";
        } elseif (array_key_exists("my_chat_member", $this->data)) {
            $this->messageType = "my_chat_member";
        } else {
            $this->messageType = "unknown message type";
        }


        return $this->messageType;
    }

    /**
     * Лишить пользователя прав
     * @return Http 
     */
    public function restrictUser(int $time): array
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "user_id" => $this->data[$this->messageType]["from"]["id"],
                "can_send_messages" => false,
                "can_send_documents" => false,
                "can_send_photos" => false,
                "can_send_videos" => false,
                "can_send_video_notes" => false,
                "can_send_other_messages" => false,
                "until_date" => $time
            ]
        )->json();

        return $response;
    }


    public function deleteMessage(): array
    {
        // dd("deleteMessage");
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "message_id" => $this->data[$this->messageType]["message_id"]
            ]
        )->json();


        return $response;
    }



    public function sendMessage(string $message): array
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "text" => $message
            ]
        )->json();
        return $response;
    }

    public function banUser(): bool
    {
        log::info("inside banNewUser");
        try {
            $this->restrictUser(time() + 86400);
            $this->sendMessage("Пользователь " . $this->data[$this->messageType]["from"]["first_name"] . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            log::info("ban new user must be success");
            return true;
        } catch (Exception $e) {
            log::info($e->getMessage());
            return false;
        }
    }

    public function blockNewVisitor(): bool
    {
        if ($this->messageType === "") {
            throw new Exception("Тип сообщения — пустая строка. Тип не задан в TelegramBotService.");
        }

        if (
            $this->messageType !== "message" &&
            $this->messageType !== "edited_message"
        ) {
            log::info("Ошибка: не текстовое сообщение\n" . __METHOD__ . "\n", $this->data);
            return false;
        }

        if (!array_key_exists("new_chat_participant", $this->data[$this->messageType])) {
            log::info("new chat participant value не существует (blocknewvisitor");
            return false;
        } else {
            $response = $this->restrictUser(time() + 86400);
            log::info("in the end of blocknewvisitor");

            if ($response["ok"] === true) {
                return true;
            };
        }

        return false;
    }
}
