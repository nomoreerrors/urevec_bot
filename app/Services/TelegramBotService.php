<?php

namespace App\Services;

use App\Classes\BaseBotCommandCore;
use App\Classes\Commands\BaseCommand;
use InvalidArgumentException;
use App\Classes\PrivateChatCommandCore;
use App\Enums\ResTime;
use App\Classes\ChatSelector;
use App\Exceptions\BanUserFailedException;
use App\Models\Admin;
use App\Classes\Menu;
use App\Exceptions\RestrictMemberFailedException;
use App\Exceptions\SetCommandsFailedException;
use App\Models\MessageModels\TextMessageModel;
use App\Models\NewUserRestriction;
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

    private $admin = null;

    private $textCommands = null;

    private ResTime $chatRestrictionTime = ResTime::DAY;

    private ?string $privateChatCommand = null;

    private ?Menu $menu = null;

    private ?ChatSelector $chatSelector = null;

    private ?BaseBotCommandCore $commandHandler = null;


    public function __construct(private TelegramRequestModelBuilder $requestModel)
    {
        $this->setAdmin()
            ->setPrivateChatCommand();
        $this->setChatSelector();
        $this->setCommandHandler();
        $this->setPrivateChatMenu();
    }

    /**
     * Restrict member
     * @param int $id 
     * @return array
     */
    public function restrictChatMember(ResTime $resTime = null, int $id = null): bool
    {
        $time = $resTime ?? $this->chatRestrictionTime;
        $until_date = time() + $time->value;

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => $this->requestModel->getChatId(),
                "user_id" => $id ?? $this->requestModel->getFromId(),
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
            log::info(CONSTANTS::MEMBER_BLOCKED . " " . $this->requestModel->getFromId() .
                " " . $time->getHumanRedable());
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
    public function sendMessage(string $text_message, ?array $reply_markup = null): void
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
        // dd("here");
        if ($response->ok()) {
            log::info($text_message . json_encode($params, JSON_UNESCAPED_UNICODE));
            return;
        }
        throw new BaseTelegramBotException(CONSTANTS::SEND_MESSAGE_FAILED, __METHOD__);
    }

    /**
     * Summary of banUser
     * @param ResTime $resTime
     * @throws \App\Exceptions\BanUserFailedException
     * @return bool
     */
    public function banUser(ResTime $resTime = null): bool
    {
        $time = $resTime ?? $this->chatRestrictionTime;
        $result = $this->restrictChatMember($time);

        if ($result) {
            $this->sendMessage("Пользователь " . $this->requestModel->getFromUserName() . " заблокирован на 24 часа за нарушение правил чата.");
            $this->deleteMessage();
            return true;
        }
        throw new BanUserFailedException(CONSTANTS::BAN_USER_FAILED, __METHOD__);
    }

    public function createChat(): void
    {
        $this->chat = Chat::create([
            "chat_id" => $this->requestModel->getChatId(),
            "chat_title" => $this->requestModel->getChatTitle(),
        ]);

        $this->chat->newUserRestrictions()->create();
        $this->chat->badWordsFilter()->create();
        $this->chat->unusualCharsFilter()->create();

        // Create admins in admins table and attaching them to the chat id from the incoming request   
        foreach ($this->requestModel->getAdmins() as $admin) {
            $adminModel = Admin::where('admin_id', $admin['admin_id'])->exists()
                ? Admin::where('admin_id', $admin['admin_id'])->first()
                : Admin::create($admin);
            $adminModel->chats()->attach($this->chat->id);
        }

        $this->setChat($this->chat->chat_id);
    }

    /**
     * Setting menu commands for the bot in private and group chats.
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
    public function sendPost(string $method, array $data): Response
    {
        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/{$method}",
            $data
        );

        return $response;
    }

    public function setChat(int $chatId): void
    {
        $this->chat = Chat::with("newUserRestrictions", "badWordsFilter", "unusualCharsFilter", "admins")
            ->where("chat_id", $chatId)->first();
        $this->setChatRestrictionTime();
    }

    public function getChat()
    {
        return $this->chat;
    }


    private function setChatRestrictionTime(): void
    {
        $time = $this->chat->newUserRestrictions->restriction_time;
        $this->chatRestrictionTime = ResTime::from($time);
    }

    private function setAdmin(): static
    {
        $this->admin = Admin::where('admin_id', $this->requestModel->getFromId())->first();
        return $this;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public function setPrivateChatCommand(?string $command = null): static
    {
        if (
            $this->requestModel->getChatType() !== "private" ||
            !($this->requestModel instanceof TextMessageModel)
        ) {
            $this->privateChatCommand = null;
            return $this;
        }

        $this->privateChatCommand = $command ?? $this->requestModel->getText();
        return $this;
    }

    public function getPrivateChatCommand()
    {
        return $this->privateChatCommand;
    }

    public function getRequestModel(): TelegramRequestModelBuilder
    {
        return $this->requestModel;
    }

    private function setPrivateChatMenu()
    {
        if ($this->requestModel->getChatType() === "private") {
            $this->menu = new Menu($this);
        }
    }

    /**
     * Get bot private chat menu
     * @return Menu|null
     */
    public function menu(): ?Menu
    {
        return $this->menu;
    }

    private function setChatSelector(): void
    {
        if ($this->requestModel->getChatType() === "private") {
            $this->chatSelector = new ChatSelector($this);
        }
    }

    public function chatSelector(): ?ChatSelector
    {
        return $this->chatSelector;
    }

    private function setCommandHandler(): void
    {
        if ($this->requestModel->getChatType() === "private") {
            $this->commandHandler = new PrivateChatCommandCore($this);
        }
    }

    public function commandHandler(): ?BaseBotCommandCore
    {
        return $this->commandHandler;
    }


    public function createCommand(string $className): ?BaseCommand
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Invalid command class: $className");
        }
        return new $className($this);
    }
}





