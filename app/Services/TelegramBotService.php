<?php

namespace App\Services;

use App\Exceptions\BanUserFailedException;
use App\Models\Admin;
use App\Exceptions\RestrictMemberFailedException;
use App\Exceptions\SetCommandsFailedException;
use App\Models\TelegramRequestModelBuilder;
use App\Models\ChatAdmins;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
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
    private $chat = null;

    private $textCommands = null;

    public function __construct(private TelegramRequestModelBuilder $requestModel)
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
        $http = app(Http::class);
        $http;
        $response = $http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/deleteMessage",
            [
                "chat_id" => $this->requestModel->getChatId(),
                "message_id" => $this->requestModel->getMessageId()
            ]
        );
        // dd($response->json());
        $response->json();

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
    public function sendMessage(string $text_message, $reply_markup = null): void
    {
        $params = [
            "chat_id" => $this->requestModel->getChatId(),
            "text" => $text_message
        ];

        if ($reply_markup) {
            $params["reply_markup"] = $reply_markup;
        }

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/sendMessage",
            $params
        );

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

    public function createChat(): Chat
    {
        // Create chat in bot_chats table
        $this->chat = Chat::create([
            "chat_id" => $this->requestModel->getChatId(),
            "chat_title" => $this->requestModel->getChatTitle(),
        ]);

        // Create admins in admins table and attaching them to the chat id from the incoming request   
        foreach ($this->requestModel->getAdmins() as $admin) {
            $admin = Admin::create($admin);
            $admin->chats()->attach($this->chat->id);
        }

        return $this->chat;
    }

    /**
     * Set menu commands for the bot in private and group chats.
     *
     * @return void
     * @throws BaseTelegramBotException
     */
    public function setMyCommands(): void
    {
        $this->setGroupChatCommandsForAdmins();
        $this->setPrivateChatCommandsForAdmins();

        $chatAdmins = $this->chat->admins;
        foreach ($chatAdmins as $admin) {
            $admin->pivot->update(['my_commands_set' => 1]);
        }
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
        $adminsIdsArray = $this->requestModel->getAdminsIds();
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

        $this->chat->admins()->update([
            "private_commands_access" => 1
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

        $this->chat->admins()->update([
            "group_commands_access" => 1
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

        for ($i = 0; $i < count($commands["commands"]); $i++) {

            $result = array_diff($updatedCommands[$i], $commands["commands"][$i]);

            if (!empty($result)) {
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

    public function setChat(int $chatId): void
    {
        $this->chat = Chat::where("chat_id", $chatId)->first();
    }
}

