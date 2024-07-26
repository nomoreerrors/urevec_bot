<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Exceptions\RestrictMemberFailedException;
use App\Exceptions\SetCommandsFailedException;
use App\Models\BaseTelegramRequestModel;
use Illuminate\Support\Facades\Cache;
use App\Models\Eloquent\BotChat;
use Illuminate\Http\Client\Response;
use App\Classes\CommandBuilder;
use App\Models\InvitedUserUpdateModel;
use App\Models\MessageModels\MessageModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CONSTANTS;
use App\Exceptions\BaseTelegramBotException;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\Configuration\Constant;

class TelegramBotService
{
    private $chatModel = null;

    private $textCommands = null;

    public function __construct(private BaseTelegramRequestModel $requestModel)
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
                "chat_id" => $this->requestModel->getChatId(),
                "user_id" => $id > 0 ? $id : $this->requestModel->getFromId(),
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
        if (!($this->requestModel instanceof MessageModel)) {
            throw new BaseTelegramBotException(CONSTANTS::DELETE_MESSAGE_FAILED .
                CONSTANTS::WRONG_INSTANCE_TYPE, __METHOD__);
        }

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
            [
                "chat_id" => $this->requestModel->getChatId(),
                "message_id" => $this->requestModel->getMessageId()
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
            "chat_id" => $this->requestModel->getChatId(),
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
            $this->sendMessage("Пользователь " . $this->requestModel->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            return true;
        }
        throw new BanUserFailedException(CONSTANTS::BAN_USER_FAILED, __METHOD__);
    }

    public function createChat(): BotChat
    {
        $this->chatModel = BotChat::create([
            "chat_id" => $this->requestModel->getChatId(),
            "chat_title" => $this->requestModel->getChatTitle(),
            "chat_admins" => $this->requestModel->getAdminsIds(),
        ]);

        // $this->setMyCommands();
        return $this->chatModel;
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
        $this->setGroupChatCommandsForAdmins();
        $this->setPrivateChatCommandsForAdmins();

        $this->chatModel->update([
            "my_commands_set" => 1
        ]);
    }

    public function getMyCommands(string $type, int $chatId): array
    {
        $scope = [
            "scope" => [
                "type" => $type,
                "chat_id" => $chatId
            ]
        ];

        $response = $this->sendPost("getMyCommands", $scope);
        // dd($response->json());
        return $response->json();
    }

    /**
     * Set bot commands visibility in a private chat for admins
     *
     * @param int $adminId
     * @return void
     * @throws BaseTelegramBotException
     */
    public function setPrivateChatCommandsForAdmins(): void
    {
        $adminsIdsArray = $this->chatModel->chat_admins;
        $moderationSettings = app("commandsList")->moderationSettings;

        if (empty($adminsIdsArray)) {
            throw new BaseTelegramBotException(
                CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED .
                CONSTANTS::EMPTY_ADMIN_IDS_ARRAY,
                __METHOD__
            );
        }

        foreach ($adminsIdsArray as $adminId) {
            $commands = (new CommandBuilder($adminId))
                ->command($moderationSettings->command, $moderationSettings->description)
                ->withChatScope()
                ->get();

            $response = $this->sendPost("setMyCommands", $commands);

            if (!$response->ok()) {
                throw new BaseTelegramBotException(CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED, __METHOD__);
            }

            $this->checkifCommandsAreSet($adminId, $commands);
        }

        $this->chatModel->update([
            "private_commands_access" => $adminsIdsArray
        ]);
    }

    /**
     * Set bot commands in a group chat for all admins by typing "/"
     * @throws BaseTelegramBotException
     * @return void
     */
    public function setGroupChatCommandsForAdmins(): void
    {
        $testCommand = app("commandsList")->testCommand;

        $commands = (new CommandBuilder($this->requestModel->getChatId()))
            ->command($testCommand->command, $testCommand->description)
            ->command($testCommand->command, $testCommand->description)
            ->withChatAdministratorsScope()
            ->get();

        $response = $this->sendPost("setMyCommands", $commands);

        if (!$response->ok()) {
            throw new BaseTelegramBotException(CONSTANTS::SET_GROUP_CHAT_COMMANDS_FAILED, __METHOD__);
        }
        $this->chatModel->update([
            "group_commands_access" => "admins"
        ]);
    }

    /**
     * Make sure that recieved commands list is the same as expected
     * @param int $chatId
     * @param array $commands
     * @throws \App\Exceptions\BaseTelegramBotException
     * @return void
     */
    public function checkifCommandsAreSet(int $chatId, array $commands): static
    {
        $updatedCommands = $this->getMyCommands("chat", $chatId)["result"];
        // dd($updatedCommands);

        for ($i = 0; $i < count($commands["commands"]); $i++) {
            // dd($updatedCommands[$i], $commands["commands"][$i]);
            $result = array_diff($updatedCommands[$i], $commands["commands"][$i]);

            if (!empty($result)) {
                // dd('here');
                throw new SetCommandsFailedException($commands, $updatedCommands);
            }
        }
        return $this;
    }

    /**
     * Summary of sendPost
     * @param mixed $method Example: setMyCommands
     * @param mixed $data
     * @return mixed
     */
    public function sendPost($method, $data): Response
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/{$method}",
            $data
        );

        return $response;
    }
}
