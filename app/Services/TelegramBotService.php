<?php

namespace App\Services;

use App\Classes\BaseBotCommandCore;
use App\Classes\ChatBuilder;
use App\Classes\Commands\BaseCommand;
use App\Classes\CommandsList;
use App\Classes\PrivateChatCommandRegister;
use App\Exceptions\DeleteUserFailedException;
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
use App\Exceptions\RestrictChatMemberFailedException;
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

    private ?ChatBuilder $chatBuilder = null;

    private ?PrivateChatCommandRegister $privateChatCommandRegister = null;



    /**
     * Name of the filter model in camel case
     * Used to determine which model's restrictions should be checked
     * to decide if a user should be restricted
     * @var string
     */
    private string $banReasonModelName = "";


    public function __construct(private TelegramRequestModelBuilder $requestModel)
    {
        $this->setAdmin()
            ->setPrivateChatCommand();
        $this->setChatSelector();
        $this->setChatBuilder();
        $this->setPrivateChatCommandRegister();
        $this->setCommandHandler();
        $this->setPrivateChatMenu();
    }

    /**
     * Restrict member
     * @param int $id 
     * @return void 
     */
    public function restrictChatMember(ResTime $resTime = null, int $id = null): void
    {
        $time = $resTime ?? $this->chatRestrictionTime;
        $until_date = time() + $time->value;

        if (empty($this->banReasonModelName)) {
            throw new RestrictChatMemberFailedException(null, __METHOD__);
        }

        $reasonModel = $this->getChat()->{$this->banReasonModelName};
        $canSendMedia = $reasonModel->can_send_media;
        $canSendMessages = $reasonModel->can_send_messages;

        $response = Http::post(
            env('TELEGRAM_API_URL') . env('TELEGRAM_API_TOKEN') . "/restrictChatMember",
            [
                "chat_id" => $this->requestModel->getChatId(),
                "user_id" => $id ?? $this->requestModel->getFromId(),
                "can_send_messages" => $canSendMessages,
                "can_send_documents" => $canSendMedia,
                "can_send_photos" => $canSendMedia,
                "can_send_videos" => $canSendMedia,
                "can_send_video_notes" => $canSendMedia,
                "can_send_other_messages" => $canSendMedia,
                "until_date" => $until_date
            ]
        );

        if (!$response->Ok()) {
            throw new RestrictChatMemberFailedException(null, __METHOD__);
        }
        return;
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

        if ($response->ok()) {
            return;
        } else {
            throw new BaseTelegramBotException(CONSTANTS::DELETE_MESSAGE_FAILED, __METHOD__);
        }
    }

    /**
     * Delete user from chat
     * @return Response 
     * @throws BaseTelegramBotException
     */
    public function deleteUser(): array
    {
        $data = [
            "chat_id" => $this->getRequestModel()->getChatId(),
            "user_id" => $this->getRequestModel()->getFromId()
        ];

        $response = $this->sendPost('banChatMember', $data);

        if ($response->ok()) {
            return $response->json();
        }
        throw new DeleteUserFailedException(CONSTANTS::DELETE_USER_FAILED, __METHOD__);
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
     * @return void 
     */
    public function banUser(ResTime $resTime = null): void
    {
        if ($this->shouldDeleteUser()) {
            $this->deleteUser();
            $this->sendMessage($this->getRequestModel()->getFromUserName() . " удален за нарушение правил чата.");
            return;
        }

        $time = $resTime ?? $this->getChatRestrictionTime();
        $this->restrictChatMember($time);
        $this->sendMessage($this->getRequestModel()->getFromUserName() . " заблокирован на " . $time->getRussianReply() . " за нарушение правил чата.");
    }


    /**
     * Set an existing chat and update its relations if needed
     * @param int $chatId
     * @return void
     */
    public function setChat(int $chatId): void
    {
        $this->chat = Chat::with($this->chatBuilder()->getChatRelationsNames())
            ->where("chat_id", $chatId)->first();

        $this->chatBuilder()->updateChatRelations();
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



    public function getChat()
    {
        return $this->chat;
    }


    // private function setChatRestrictionTime(): void
    // {
    //     // $time = $this->chat->newUserRestrictions->restriction_time;
    //     // $this->chatRestrictionTime = ResTime::from($time);
    //     $this->chatRestrictionTime = ResTime::TWO_HOURS;
    // }

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
     * Get bot private chat menu property
     * @return Menu|null
     */
    public function menu(): ?Menu
    {
        return $this->menu;
    }

    /**
     * Get ChatSelector class property
     * @return void
     */
    private function setChatSelector(): void
    {
        if ($this->requestModel->getChatType() === "private") {
            $this->chatSelector = new ChatSelector($this);
        }
    }

    private function setChatBuilder(): void
    {
        $this->chatBuilder = new ChatBuilder($this);
    }

    public function chatBuilder(): ?ChatBuilder
    {
        return $this->chatBuilder;
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

    /**
     * Create command class instance 
     * @param string $className Example:  BadWordsFilterCommand extends BaseCommand
     * @throws \InvalidArgumentException
     * @return object
     */
    public function createCommand(string $className): ?BaseCommand
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Invalid command class: $className");
        }
        return new $className($this);
    }

    public function setBanReasonModelName(string $reason): void
    {
        $this->banReasonModelName = $reason;
    }

    public function getBanReasonModelName(): string
    {
        return $this->banReasonModelName;
    }

    public function getChatRestrictionTime(): ResTime
    {
        return $this->chatRestrictionTime;
    }

    /**
     * @return bool
     */
    protected function shouldDeleteUser(): bool
    {
        $relation = $this->getChat()->{$this->getBanReasonModelName()};
        return $relation->first()->delete_user;
    }

    protected function setPrivateChatCommandRegister(): void
    {
        // if ($this->requestModel->getChatType() === "private") {
        $this->privateChatCommandRegister = new PrivateChatCommandRegister($this);
        // }
    }

    public function privateChatCommandRegister()
    {
        return $this->privateChatCommandRegister;
    }

    protected function commandsList()
    {
        return new CommandsList();
    }

}





