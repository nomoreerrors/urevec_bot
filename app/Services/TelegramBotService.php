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
use Illuminate\Support\Facades\Config;

class TelegramBotService
{

    public $data;

    public string $messageType = "";

    private int $day = 86400;


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
        // log::info("inside links filter");
        if (
            $this->messageType !== "message" &&
            $this->messageType !== "edited_message"
        ) {

            return false;
        }
        if (array_key_exists("entities", $this->data[$this->messageType])) {
            $result = str_contains(json_encode($this->data[$this->messageType]["entities"]), "text_link");
            if ($result) {
                return true;
            }
        }


        if (array_key_exists("text", $this->data[$this->messageType])) {
            $hasLink = str_contains($this->data[$this->messageType]["text"], "http");

            if ($hasLink) {
                return true;
            }
        }
        // dd($this->data);
        return false;
    }




    public function checkIfUserIsAdmin(): bool
    {


        $adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));

        $result = false;
        if (array_key_exists($this->messageType, $this->data)) {
            log::info($adminsIdArray);
            log::info($this->data[$this->messageType]["from"]["id"]);
            if ((string) in_array($this->data[$this->messageType]["from"]["id"], $adminsIdArray)) {

                $result = true;

                Log::info("USER IS ADMIN!!!!!!!" . $this->data[$this->messageType]["from"]["id"]);
            } else {
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
        } elseif (array_key_exists("chat_member", $this->data)) {
            $this->messageType = "chat_member";
        } elseif (array_key_exists("my_chat_member", $this->data)) {
            $this->messageType = "my_chat_member";
        } else {
            $this->messageType = "unknown message type";
            log::info($this->messageType, $this->data);
        }


        return $this->messageType;
    }


    public function checkIfMessageForwardFromAnotherGroup(): bool
    {
        if ($this->messageType === "message" || $this->messageType === "edited_message") {
            if (
                array_key_exists("forward_from_chat", $this->data[$this->messageType]) &&
                array_key_exists("forward_origin", $this->data[$this->messageType])
            ) {
                // dd($this->data);
                return true;
            }
        }
        return false;
    }
    /**
     * Лишить пользователя прав
     * По умолчанию: user id объекта request
     * @return array
     */
    public function restrictUser(int $time = 86400, int $id = 0,): bool
    {

        $until_date = time() + $time;
        $result = false;



        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "user_id" => $id > 0 ? $id : $this->data[$this->messageType]["from"]["id"],
                "can_send_messages" => false,
                "can_send_documents" => false,
                "can_send_photos" => false,
                "can_send_videos" => false,
                "can_send_video_notes" => false,
                "can_send_other_messages" => false,
                "until_date" => $until_date
            ]
        )->json();

        log::info("restrict until_date restrictUser: " . $until_date);

        $result = $response["ok"];

        return $result;
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

    public function banUser(int $time = 86400): bool
    {
        log::info("inside banNewUser");

        $this->restrictUser($time);
        $this->sendMessage("Пользователь " . $this->data[$this->messageType]["from"]["first_name"] . " заблокирован на 24 часа за нарушение правил чата.");
        $this->deleteMessage();
        // dd($time);
        log::info("time from banUser: " . $time);
        return true;
    }



    public function checkIfIsNewMember(): bool
    {
        if ($this->messageType === "") {
            throw new Exception("Тип сообщения — пустая строка. Тип не задан в TelegramBotService.");
        }

        if ($this->messageType !== "chat_member") {
            return false;
        }

        if (!array_key_exists("new_chat_member", $this->data[$this->messageType])) {
            log::info("new_chat_member value не существует (blocknewvisitor");
            return false;
        }


        if ($this->data[$this->messageType]["new_chat_member"]["status"] !== "member") {
            //Не является новым подписчиком
            log::info("new_chat_member status !== member", $this->data);
            return false;
        }
        return true;
    }




    /**
     * Временная блокировка новых подписчиков, включая приглашенных
     * @throws \Exception
     * @return bool
     */
    public function blockNewVisitor(): bool
    {
        $isNewMember = $this->checkIfIsNewMember();

        // dd($isNewMember);
        if ($isNewMember) {

            if (!array_key_exists("user", $this->data[$this->messageType]["new_chat_member"])) {
                throw new Exception("Ключ user не существует. Возможно, объект более сложный 
                 приглашено несколько подписчиков одновременно");
            }


            //Подписчик кого-то пригласил. Блокировка приглашенного подписчика.
            //TODO: Поймать объект с несколькими приглашенными одновременно и обработать
            if ($this->data[$this->messageType]["new_chat_member"]["user"]["id"] !== $this->data[$this->messageType]["from"]["id"]) {
                $result = $this->restrictUser(id: $this->data[$this->messageType]["new_chat_member"]["user"]["id"]);

                if ($result) {
                    log::info("Invited user blocked. Chat_member status: " . $this->data[$this->messageType]["new_chat_member"]["status"]);

                    return true;
                }
            }


            $result = $this->restrictUser();
            if ($result) {

                log::info("User blocked. Chat_member status: " . $this->data[$this->messageType]["new_chat_member"]["status"]);

                return true;
            }
        }
        return false;
    }
}
