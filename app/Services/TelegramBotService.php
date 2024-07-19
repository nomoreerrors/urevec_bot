<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseMediaModel;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Cache;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\StatusUpdateModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\TextMessageModel;


class TelegramBotService
{
    public function __construct(private BaseTelegramRequestModel $model)
    {
    }

    /**
     * Restrict member
     * @param int $id 
     * @return array
     */
    public function restrictChatMember(int $time = null, int $id = null): bool
    {
        if (empty($time)) {
            $time = Cache::get("new_users_restriction_time");
        }
        $until_date = time() + $time;

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => $this->model->getChatId(),
                "user_id" => $id > 0 ? $id : $this->model->getFromId(),
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
            // dd($this->model->getChatId(), $response);
            // dd($response);
            return false;
        }
    }

    /**
     * Summary of deleteMessage
     * @return bool
     */
    public function deleteMessage(): bool
    {
        if ($this->model instanceof MessageModel) {
            $response = Http::post(
                env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
                [
                    "chat_id" => $this->model->getChatId(),
                    "message_id" => $this->model->getMessageId()
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
    public function sendMessage(string $text_message, $reply_markup = null): array
    {
        $params = [
            "chat_id" => $this->model->getChatId(),
            "text" => $text_message
        ];

        if ($reply_markup) {
            $params["reply_markup"] = $reply_markup;
        }

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            $params
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
        if ($result) {
            $this->sendMessage("Пользователь " . $this->model->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            return true;
        }
        throw new BanUserFailedException(CONSTANTS::BAN_USER_FAILED, __METHOD__);
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
            "FROM USER NAME" => $this->model->getFromUserName(),
            "MESSAGE TYPE" => $this->model->getType(),
        ];

        if ($this->model instanceof MessageModel) {
            $data["0"]["FROM ADMIN"] = $this->model->getFromAdmin();
            $data["0"]["FROM USER ID"] = $this->model->getFromId();
            $data["0"]["MESSAGE_ID"] = $this->model->getFromId();
            $data["0"]["MESSAGE HAS TEXT_LINK"] = $this->model->getHasTextLink();
            if ($this->model instanceof TextMessageModel) {
                $data["0"]["MESSAGE HAS LINK"] = $this->model->getHasLink();
                $data["0"]["TEXT"] = $this->model->getText();
            }
        }

        if ($this->model instanceof StatusUpdateModel) {
            $data["0"]["MESSAGE IS STATUS UPDATE(CHAT_MEMBER)"] = true;
            $data["0"]["NEW CHAT MEMBER STATUS"] = $this->model->getData()["chat_member"]["new_chat_member"]["status"];
            $data["0"]["NEW MEMBER JOIN UPDATE"] = false;

            if ($this->model instanceof NewMemberJoinUpdateModel) {
                $data["0"]["NEW MEMBER JOIN UPDATE"] = true;
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->model->getData()["chat_member"]["new_chat_member"]["status"];
            }

            if ($this->model instanceof InvitedUserUpdateModel) {
                $data["0"]["MESSAGE IS INVITE USER UPDATE"] = true;
                $data["0"]["INVITED USERS ARRAY"] = $this->model->getInvitedUsersIdArray();
                $data["0"]["NEW CHAT MEMBER STATUS"] = $this->model->getData()["chat_member"]["new_chat_member"]["status"];
            }
        }

        if (!$requestLog) {
            Storage::put("pretty_request_log.json", json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            $requestLog[] = $data;
            Storage::put("pretty_request_log.json", json_encode($requestLog, JSON_UNESCAPED_UNICODE));
        }
    }

}
