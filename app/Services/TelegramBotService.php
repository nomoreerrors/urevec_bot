<?php
namespace App\Services;

use App\Classes\BaseBotCommandCore;
use App\Enums\RestrictChatMemberData;
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
use App\Traits\RestrictChatMemberTrait;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\Configuration\Constant;


class TelegramBotService
{
    use RestrictChatMemberTrait;

    private $chat = null;

    private $admin = null;

    private $textCommands = null;

    private ?string $privateChatCommand = null;

    private ?Menu $menu = null;

    private ?ChatSelector $chatSelector = null;

    private ?BaseBotCommandCore $commandHandler = null;

    private ?ChatBuilder $chatBuilder = null;

    private ?PrivateChatCommandRegister $privateChatCommandRegister = null;

    private $requestModel = null;


    /**
     * Name of the filter model in camel case
     * Used to determine which model's restrictions should be checked
     * to decide if a user should be restricted
     * @var string
     */
    private string $banReasonModelName = "";


    public function __construct()
    {
    }

    /**
     * The general method that should be called to initialize the service
     * @param \App\Models\TelegramRequestModelBuilder $requestModel
     * @return void
     */
    public function setRequestModel(TelegramRequestModelBuilder $requestModel): void
    {
        $this->requestModel = $requestModel;
        $this->init();
    }

    public function init()
    {
        $this->setAdmin()
            ->setPrivateChatCommand();
        $this->setChatSelector();
        $this->setChatBuilder();
        // $this->setPrivateChatCommandRegister();
        $this->setCommandHandler();
        $this->setPrivateChatMenu();
    }


    /**
     * Summary of sendMessage
     * @param string $text_message
     * @param array $reply_markup reply parameters of Telegram Api
     * @return void
     * @throws BaseTelegramBotException
     */
    public function sendMessage(string $text_message, ?array $reply_markup = null): void
    {
        $params = [
            "chat_id" => $this->getRequestModel()->getChatId(),
            "text" => $text_message
        ];

        if ($reply_markup) {
            $params["reply_markup"] = $reply_markup;
        }

        $response = $this->sendPost('sendMessage', $params);

        if ($response->ok()) {
            return;
        }
        log::info($text_message . json_encode($params, JSON_UNESCAPED_UNICODE) . "chat_id: " . $this->getRequestModel()->getChatId());
        throw new BaseTelegramBotException(CONSTANTS::SEND_MESSAGE_FAILED, __METHOD__);
    }

    /**
     * Summary of deleteMessage
     * @return void
     * @throws BaseTelegramBotException
     */
    public function deleteMessage(): void
    {
        if (!($this->getRequestModel() instanceof MessageModel)) {
            throw new BaseTelegramBotException(
                CONSTANTS::DELETE_MESSAGE_FAILED .
                CONSTANTS::WRONG_INSTANCE_TYPE,
                __METHOD__
            );
        }

        $data = [
            "chat_id" => $this->getRequestModel()->getChatId(),
            "message_id" => $this->getRequestModel()->getMessageId()
        ];

        $response = $this->sendPost('deleteMessage', $data);


        if ($response->ok()) {
            return;
        } else {
            log::info(CONSTANTS::DELETE_MESSAGE_FAILED . json_encode($data));
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
        $j = 0;
        $this->chat = Chat::with($this->chatBuilder()->getChatRelationsNames())
            ->where("chat_id", $chatId)->first();

        $this->chatBuilder()->updateChatRelations();
    }


    // /**
    //  * Set bot commands in a group chat for all admins by typing "/"
    //  * @throws BaseTelegramBotException
    //  * @return void
    //  */
    // public function setGroupChatCommandsForAdmins(): void
    // {
    //     $testCommand = app("commandsList")->testCommand;

    //     $commands = (new CommandBuilder($this->requestModel->getChatId()))
    //         ->command($testCommand->command, $testCommand->description)
    //         ->command($testCommand->command, $testCommand->description)
    //         ->withChatAdministratorsScope()
    //         ->get();

    //     $response = $this->sendPost("setMyCommands", $commands);

    //     if (!$response->ok()) {
    //         throw new BaseTelegramBotException(CONSTANTS::SET_GROUP_CHAT_COMMANDS_FAILED, __METHOD__);
    //     }

    //     $this->chat->admins()->update([
    //         "group_commands_access" => 1
    //     ]);
    // }

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

    public function setChatBuilder(?ChatBuilder $chatBuilder = null): void
    {
        $this->chatBuilder = $chatBuilder ? $chatBuilder : new ChatBuilder($this);
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
     * Create command class instance from app/classes/Commands directory according to a class name  and execute the command
     * The handle method runs in parent constructor (BaseCommand::class) and command should be executed
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
        if (empty($this->getChat())) {
            throw new BaseTelegramBotException(CONSTANTS::SET_PRIVATE_CHAT_COMMANDS_FAILED, __METHOD__);
        }

        if (!$this->privateChatCommandRegister) {
            $this->setPrivateChatCommandRegister();
            return $this->privateChatCommandRegister;
        } else {
            return $this->privateChatCommandRegister;
        }
    }

    protected function commandsList()
    {
        return new CommandsList();
    }

}





