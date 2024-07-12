<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseTelegramRequestModel;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use Symfony\Component\HttpFoundation\Response;
use App\Models\NewMemberJoinUpdateModel;
use App\Exceptions\TelegramModelError;
use App\Exceptions\TelegramModelException;
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
        $this->message = $message;
    }



    /**
     * Summary of prettyRequestLog
     * @return void
     */
    public function prettyRequestLog()
    {
        $requestLog = Storage::json("pretty_request_log.json");
        $time = date("F j, Y, g:i a");


        $data["0"] = [
            "TIME" => $time,
            "FROM USER NAME" => $this->message->getFromUserName(),
            "MESSAGE TYPE" => $this->message->getType(),
        ];


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
            $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->getData()["chat_member"]["new_chat_member"]["status"];
            $data["0"]["NEW MEMBER JOIN UPDATE"] = false;

            if ($this->message instanceof NewMemberJoinUpdateModel) {

                $data["0"]["NEW MEMBER JOIN UPDATE"] = true;
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->getData()["chat_member"]["new_chat_member"]["status"];
            }

            if ($this->message instanceof InvitedUserUpdateModel) {

                $data["0"]["MESSAGE IS INVITE USER UPDATE"] = true;
                $data["0"]["INVITED USERS ARRAY"] = $this->message->getInvitedUsersIdArray();
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->message->getData()["chat_member"]["new_chat_member"]["status"];
            }
        }


        if (!$requestLog) {
            Storage::put("pretty_request_log.json", json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            $requestLog[] = $data;

            Storage::put("pretty_request_log.json", json_encode($requestLog, JSON_UNESCAPED_UNICODE));
        }
    }


    /**
     * Restrict member
     * @param int $id 
     * @return array
     */
    public function restrictChatMember(int $time = 86400, int $id = 0): bool
    {
        $until_date = time() + $time;

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => $this->message->getChatId(),
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
            return false;
        }
    }


    /**
     * Summary of deleteMessage
     * @return bool
     */
    public function deleteMessage(): bool
    {
        if ($this->message instanceof MessageModel) {
            $response = Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
                [
                    "chat_id" => $this->message->getChatId(),
                    "message_id" => $this->message->getMessageId()
                ]
            )->json();

            if ($response["ok"]) {
                return true;
            }
        }
        return false;
    }


    /**
     * Summary of sendMessage
     * @param string $text_message
     * @return array $response
     */
    public function sendMessage(string $text_message): array
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            [
                "chat_id" => $this->message->getChatId(),
                "text" => $text_message
            ]
        )->json();
        return $response;
    }


    /**
     * Summary of banUser
     * @param int $time 24 hours by default
     * @throws \App\Exceptions\BanUserFailedException
     * @return bool
     */
    public function banUser(int $time = 86400): bool
    {
        log::info("inside banNewUser");

        $result = $this->restrictChatMember($time);
        if($result) {
            $this->sendMessage("Пользователь " . $this->message->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            return true;
        }

        throw new BanUserFailedException(CONSTANTS::BAN_USER_FAILED, __METHOD__);
    }





    /**
     * Summary of blockNewVisitor
     * Restrict new member for 24 hours
     * @throws \App\Exceptions\RestrictMemberFailedException
     * @return bool
     */
    public function blockNewVisitor(): bool
    {
            if(!($this->message instanceof InvitedUserUpdateModel) &&
                !($this->message instanceof NewMemberJoinUpdateModel)) {

                return false;
            }

            if ($this->message instanceof NewMemberJoinUpdateModel) {

                    $result = $this->restrictChatMember();

                    if ($result) {
                        log::info(CONSTANTS::NEW_MEMBER_RESTRICTED . "user_id: " . $this->message->getFromId());
                        return true;
                    }
            }


            if ($this->message instanceof InvitedUserUpdateModel) {

                $invitedUsers = $this->message->getInvitedUsersIdArray();

                if ($invitedUsers !== []) {

                    foreach ($invitedUsers as $user_id) {

                        $result = $this->restrictChatMember(id: $user_id);
                        if ($result) {
                            log::info(CONSTANTS::INVITED_USER_BLOCKED . "USER_ID: " . $user_id);
                        }
                    }
                    return true;
                }
            }
        throw new RestrictMemberFailedException(CONSTANTS::RESTRICT_NEW_USER_FAILED, __METHOD__);
    }


    /**
     * Summary of blockUserIfMessageIsForward
     * Forward message from another group or chat
     * @return bool
     */
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


    public function ifMessageHasLinkBlockUser(): bool
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


    /**
     * Summary of deleteMessageIfContainsBlackListWords
     * Words are stored at Storage/app/badWord.json & badPhrases.json
     * @return bool
     */
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
