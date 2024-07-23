<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Models\BaseMediaModel;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use App\Classes\CommandBuilder;
use App\Models\ForwardMessageModel;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModel;
use App\Models\NewMemberJoinUpdateModel;
use App\Models\StatusUpdateModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\CONSTANTS;
use App\Exceptions\BaseTelegramBotException;
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
        );
        if ($response->Ok()) {
            return true;
        }
        throw new BaseTelegramBotException(CONSTANTS::RESTRICT_MEMBER_FAILED, __METHOD__);
    }

    /**
     * Summary of deleteMessage
     * @return void
     * @throws BaseTelegramBotException
     */
    public function deleteMessage(): void
    {
        if (!$this->model instanceof MessageModel) {
            throw new BaseTelegramBotException(CONSTANTS::DELETE_MESSAGE_FAILED .
                CONSTANTS::WRONG_INSTANCE_TYPE, __METHOD__);
        }

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
            [
                "chat_id" => $this->model->getChatId(),
                "message_id" => $this->model->getMessageId()
            ]
        );
        // dd($response->json());

        if ($response->ok()) {
            return;
        } else {
            throw new BaseTelegramBotException(CONSTANTS::DELETE_MESSAGE_FAILED, __METHOD__);
        }
    }

    /**
     * Summary of sendMessage
     * @param string $text_message
     * @return void
     * @throws BaseTelegramBotException
     */
    public function sendMessage(string $text_message, array $reply_markup = null): void
    {
        $params = [
            "chat_id" => $this->model->getChatId(),
            "text" => $text_message
        ];

        if ($reply_markup) {
            $params["reply_markup"] = $reply_markup;
        }

        // dd("here");
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            $params
        );
        // dd($response);
        if ($response->ok()) {
            return;
        }
        throw new BaseTelegramBotException(CONSTANTS::SEND_MESSAGE_FAILED, __METHOD__);
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
     * Set menu commands for bot in private and group chats
     * 
     * !!! Working badly with Telegram desktop app. Check status at web version or smartphone app.
     * @return void
     * @throws BaseTelegramBotException
     */
    public function setMyCommands(): void
    {
        $adminsIdsArray = Cache::get(CONSTANTS::CACHE_CHAT_ADMINS_IDS . $this->model->getChatId());

        if (empty($adminsIdsArray)) {
            throw new BaseTelegramBotException(
                CONSTANTS::SET_MY_COMMANDS_FAILED .
                CONSTANTS::CACHE_ADMINS_IDS_NOT_SET,
                __METHOD__
            );
        }

        if (
            Cache::has(CONSTANTS::CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY . $this->model->getChatId()) &&
            Cache::has(CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->model->getChatId())
        ) {
            return;
        }

        $this->setGroupChatCommandsVisibilityForAdmins();

        foreach ($adminsIdsArray as $adminId) {
            $this->setPrivateChatCommandsVisibilityForAdmins($adminId);
        }
        Cache::put(CONSTANTS::CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY . $this->model->getChatId(), $adminsIdsArray);
    }

    /**
     * Set bot commands visibility in a private chat for admins
     *
     * @param int $adminId
     * @return void
     * @throws BaseTelegramBotException
     */
    private function setPrivateChatCommandsVisibilityForAdmins(int $adminId): void
    {
        $command = (new CommandBuilder())
            ->command("command_for_private_chat", "configure private chat moderation")
            ->withScope()
            ->chat()
            ->addChatId($this->model->getChatId())
            ->get();

        $response = $this->sendPost("setMyCommands", $command);

        if (!$response["ok"]) {
            throw new BaseTelegramBotException(CONSTANTS::SET_MY_COMMANDS_FAILED, __METHOD__);
        }

    }

    /**
     * Set bot commands visibility in a group chat for admins by typing "/"
     *
     * @throws BaseTelegramBotException
     *
     * @return void
     */
    private function setGroupChatCommandsVisibilityForAdmins(): void
    {
        $cacheKey = CONSTANTS::CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY . $this->model->getChatId();

        if (Cache::has($cacheKey)) {
            return;
        }

        $command = (new CommandBuilder())
            ->command("command_for_group_chat", "configure group chat moderation")
            ->command("command_second_for_group_chat", "one more command for test")
            ->withScope()
            ->chatAdministrators()
            ->addChatId($this->model->getChatId())
            ->get();
        // dd($command);

        $response = $this->sendPost("setMyCommands", $command);
        // dd($response);
        if (!$response["ok"]) {
            throw new BaseTelegramBotException("Failed to set group chat commands visibility", __METHOD__);
        }

        Cache::put($cacheKey, "enabled");
    }

    /**
     * Summary of sendPost
     * @param mixed $method Example: setMyCommands
     * @param mixed $data
     * @return mixed
     */
    public function sendPost($method, $data): array
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/{$method}",
            $data
        )->json();

        return $response;
    }
}
