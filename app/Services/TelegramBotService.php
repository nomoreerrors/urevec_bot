<?php

namespace App\Services;

use App\Models\BaseTelegramRequestModel;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\StatusUpdateModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ErrorException;
use Exception;
use App\Models\TelegramMessageModel;
use App\Models\TextMessageModel;
use Illuminate\Support\Facades\Config;

class TelegramBotService
{

    public function __construct(private BaseTelegramRequestModel $message)
    {
        // dd("here");
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
        $time = date("F j, Y, g:i a");


        $data["0"] = [
            "TIME" => $time,
            "FROM USER NAME" => $this->message->getFromUserName(),
            "MESSAGE TYPE" => $this->message->getType(),
        ];

        // dd($this->message->hasTextLink());

        if ($this->message instanceof MessageModel) {

            $data["0"]["FROM ADMIN"] = $this->message->getFromAdmin();
            $data["0"]["FROM USER ID"] = $this->message->getFromId();
            $data["0"]["MESSAGE_ID"] = $this->message->getFromId();
            $data["0"]["MESSAGE HAS TEXT_LINK"] = $this->message->hasTextLink();
            if ($this->message instanceof TextMessageModel) {
                $data["0"]["MESSAGE HAS LINK"] =  $this->message->getHasLink();
                $data["0"]["TEXT"] = $this->message->getText();
            }
        }

        if ($this->message instanceof StatusUpdateModel) {
            $data["0"]["MESSAGE IS STATUS UPDATE(CHAT_MEMBER)"] = true;
            $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->data["chat_member"]["new_chat_member"]["status"];
            $data["0"]["NEW MEMBER JOIN UPDATE"] = false;

            if ($this->message instanceof NewMemberJoinUpdateModel) {

                $data["0"]["NEW MEMBER JOIN UPDATE"] = true;
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->data["chat_member"]["new_chat_member"]["status"];
            }

            if ($this->message instanceof InvitedUserUpdateModel) {

                $data["0"]["MESSAGE IS INVITE USER UPDATE"] = true;
                $data["0"]["INVITED USERS ARRAY"] = $this->message->getInvitedUsersIdArray();
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->data["chat_member"]["new_chat_member"]["status"];
            }
        }


        if (!$requestLog) {
            Storage::put("DONE.json", json_encode($data));
        } else {
            $requestLog[] = $data;

            Storage::put("DONE.json", json_encode($requestLog));
        }
    }


    public function saveRawRequestData(array $data)
    {
        $this->message->data = $data;

        $requestLog = Storage::json("rawrequest.json");

        if (!$requestLog) {
            Storage::put("rawrequest.json", json_encode($data));
        } else {
            $requestLog[] = $data;
            Storage::put("rawrequest.json", json_encode($requestLog));
        }
    }




    /**
     * Лишить пользователя прав
     * По умолчанию: user id объекта request
     * @return array
     */
    public function restrictChatMember(int $time = 86400, int $id = 0): bool
    {
        $until_date = time() + $time;

        // dd($this->message->getFromId());

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
            $response["description"] ===
            'Bad Request: PARTICIPANT_ID_INVALID' ||
            "Bad Request: invalid user_id specified"
        ) {
            // dd($response, "USER ID: " . $this->message->getFromId() . PHP_EOL . get_class($this->message));
            return false;
        }
        throw new Exception("Не удалось заблокировать по id");
    }


    public function deleteMessage(): bool
    {
        if ($this->message instanceof MessageModel) {
            $response = Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
                [
                    "chat_id" => env("TELEGRAM_CHAT_ID"),
                    "message_id" => $this->message->getMessageId()
                ]
            )->json();

            if ($response["ok"]) {
                return true;
            }
        }
        return false;
    }


    //Эти медоты отправки и удаления сообщений надо отсюда убрать
    //Эти медоты отправки и удаления сообщений надо отсюда убрать
    //Эти медоты отправки и удаления сообщений надо отсюда убрать
    //Эти медоты отправки и удаления сообщений надо отсюда убрать
    //Эти медоты отправки и удаления сообщений надо отсюда убрать
    //Эти медоты отправки и удаления сообщений надо отсюда убрать
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
        try {
            $this->restrictChatMember($time);

            $this->sendMessage("Пользователь " . $this->message->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            log::info("time from banUser: " . $time);
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }



    /**
     * Временная блокировка новых подписчиков, включая приглашенных
     * @throws \Exception
     * @return bool
     */
    public function blockNewVisitor(): bool
    {
        if (
            $this->message instanceof NewMemberJoinUpdateModel ||
            $this->message instanceof InvitedUserUpdateModel
        ) {


            $result = $this->restrictChatMember();


            if ($result) {
                log::info("User blocked. Message id: " .
                    $this->message->messageId . "user_id: " . $this->message->getFromId());
                return true;
            }
        }


        if ($this->message instanceof InvitedUserUpdateModel) {
            $invitedUsers = $this->message->getInvitedUsersIdArray();

            if ($invitedUsers !== []) {
                foreach ($invitedUsers as $user_id) {
                    $result = $this->restrictChatMember(id: $user_id);

                    if ($result) {
                        log::info("Invited user blocked. USER_ID: " . $user_id);
                    }
                }
                return true;
            }
        }

        // dd($this->message->data);
        return false;
    }


    public function blockUserIfMessageIsForward(): bool
    {
        if (
            $this->message instanceof ForwardMessageModel &&
            !$this->message->getFromAdmin()
        ) {
            if ($this->banUser());

            return true;
        }

        return false;
    }


    public function blockUserIfMessageHasLink(): bool
    {
        if ($this->message->getFromAdmin()) {
            return false;
        }

        if ($this->message instanceof MessageModel) {
            if ($this->message->hasTextLink()) {

                if ($this->banUser()) {
                    return true;
                };
            }
        }

        if ($this->message instanceof TextMessageModel) {
            if ($this->message->getHasLink()) {

                if ($this->banUser()) {
                    return true;
                };
            }
        }
        return false;
    }


    public function deleteMessageIfContainsBlackListWords(): bool
    {
        if (
            $this->message instanceof TextMessageModel &&
            !$this->message->getFromAdmin()
        ) {
            $filter = new FilterService($this->message);
            if ($filter->wordsFilter()) {
                $this->deleteMessage();
            }
        }
        return false;
    }
}
