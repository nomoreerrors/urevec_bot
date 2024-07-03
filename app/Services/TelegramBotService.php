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
use App\Models\TelegramMessageModel;
use Illuminate\Support\Facades\Config;

class TelegramBotService extends BaseService
{

    public function __construct(private TelegramMessageModel $message)
    {
        $this->message = $message;
    }



    /**
     * Запись входящего объекта в файл 
     * @param array $data
     * @return void
     */
    public function requestLog()
    {
        $requestLog = Storage::json("DONE.json");

        $data = [
            "IS ADMIN" => $this->message->getFromAdmin(),
            "USER ID" => $this->message->getFromId(),
            "USER NAME" => $this->message->getFromUserName(),
            "MESSAGE TYPE" => $this->message->getMessageType(),
            "MESSAGE HAS LINK" => $this->message->getHasLink(),
            "MESSAGE IS FORWARD FROM ANOTHER GROUP" => $this->message->getIsForwardMessage(),
            "NEW MEMBER JOIN UPDATE" => $this->message->getIsNewMemberJoinUpdate(),
            "INVITED USERS ID" => $this->message->getInvitedUsersId(),
        ];


        if (!$requestLog) {
            Storage::put("DONE.json", json_encode($data));
        } else {
            $requestLog[] = $data;

            Storage::put("DONE.json", json_encode($requestLog));
        }
        // dd("here");
    }




    /**
     * Лишить пользователя прав
     * По умолчанию: user id объекта request
     * @return array
     */
    public function restrictChatMember(int $time = 86400, int $id = 0): bool
    {

        $until_date = time() + $time;



        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "user_id" => $id > 0 ? $id : $this->message->getFromId(),
                "can_send_messages" => false,
                "can_send_documents" => false,
                "can_send_photos" => false,
                "can_send_videos" => false,
                "can_send_video_notes" => false,
                "can_send_other_messages" => false,
                "until_date" => $until_date
            ]
        )->json();

        if ($response["ok"]) {
            return true;
        }
        if (
            $response["ok"] === false &&
            $response["description"] === 'Bad Request: PARTICIPANT_ID_INVALID'
        ) {
            return false;
        }

        throw new Exception("Не удалось заблокировать по id");
        // return false;
    }


    public function deleteMessage(): array
    {
        // dd("deleteMessage");
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "message_id" => $this->message->getMessageId()
            ]
        )->json();


        return $response;
    }



    public function sendMessage(string $text_message): array
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            [
                "chat_id" => env("TELEGRAM_CHAT_ID"),
                "text" => $text_message
            ]
        )->json();
        return $response;
    }



    public function banUser(int $time = 86400): bool
    {
        log::info("inside banNewUser");

        $this->restrictChatMember($time);
        $this->sendMessage("Пользователь " . $this->message->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
        $this->deleteMessage();
        log::info("time from banUser: " . $time);
        return true;
    }



    /**
     * Временная блокировка новых подписчиков, включая приглашенных
     * @throws \Exception
     * @return bool
     */
    public function blockNewVisitor(): bool
    {

        //Подписчик кого-то пригласил. Блокировка приглашенного подписчика.
        //TODO: Поймать объект с несколькими приглашенными одновременно и обработать
        $invitedUsers = $this->message->getInvitedUsersId();


        if ($invitedUsers !== []) {
            foreach ($invitedUsers as $user_id) {
                $result = $this->restrictChatMember(id: $user_id);

                if ($result) {
                    log::info("Invited user blocked. : " . $this->message->getFromId());
                }
            }
            return true;
        }



        if ($this->message->getIsNewMemberJoinUpdate()) {
            $result = $this->restrictChatMember();

            if ($result) {

                log::info("User blocked. Message id: " . $this->message->messageId . "user_id" . $this->message->getFromId());

                return true;
            }
        }


        return false;
    }
}
