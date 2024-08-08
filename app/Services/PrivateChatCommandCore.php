<?php

namespace App\Services;

use App\Classes\CommandChatSelector;
use App\Classes\FilterSettingsCommand;
use App\Classes\MainMenuCommand;
use App\Enums\MainMenu;
use App\Enums\ResNewUsersCmd;
use App\Models\Chat;
use App\Classes\ModerationSettings;
use App\Classes\BaseCommand;
use App\Classes\ReplyKeyboardMarkup;
use App\Classes\RestrictNewUsersCommand;
use App\Exceptions\BaseTelegramBotException;
use App\Exceptions\UnknownChatException;
use App\Models\Admin;
use App\Models\MessageModels\TextMessageModel;
use App\Models\TelegramRequestModelBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Enums\COMMAND;

class PrivateChatCommandCore extends BaseBotCommandCore
{
    private $admin;

    private array $groupsTitles = [];

    // protected TextMessageModel $requestModel;

    public function __construct()
    {
        parent::__construct();
        //TODO  убрать, если все работает:
        //  $this->requestModel = app("requestModel"); 
        $this->admin = Admin::where('admin_id', $this->requestModel->getFromId())->first();
        $this->checkUserAccess()
            ->setGroupsTitles()
            ->handle();
    }


    protected function handle(): static
    {
        $this->chatSelectionHandler();
        if (empty($this->botService->getChat()))
            return $this;


        if (MainMenu::exists($this->command)) {
            new MainMenuCommand($this->command);
            return $this;
        }


        if (ResNewUsersCmd::exists($this->command)) {
            new RestrictNewUsersCommand($this->command);
            return $this;
        }


        //TODO заменить фильтр на русское словo
        if (str_starts_with($this->command, "/filter_")) {
            new FilterSettingsCommand($this->command);
            return $this;
        }


        app("botService")->sendMessage("Неизвестная команда");
        log::info("Неизвестная команда в приватном чате" . $this->command);
        response(CONSTANTS::UNKNOWN_CMD, 200);
        return $this;
    }

    /**
     * Send the list of available chats to user as buttons
     * @return void
     */
    private function setSelectedChat(): void
    {
        $chatId = $this->findSelectedChatId();
        $this->botService->setChat($chatId);
        $this->botService->sendMessage("Selected chat: " . $this->botService->getChat()->chat_title);
        $this->rememberLastSelectedChatId();
    }

    public function sendSelectChatButtons(): void
    {
        $keyBoard = $this->buttons->getSelectChatButtons($this->groupsTitles);
        app("botService")->sendMessage("Select chat", $keyBoard);
        return;
    }

    // private function startHandler(): Response
    // {
    //     return response();
    // }

    protected function checkUserAccess(): static
    {
        if (empty($this->admin)) {
            $error = CONSTANTS::USER_NOT_ALLOWED . " " . $this->requestModel->getChatId();
            log::info($error);

            app("botService")->sendMessage(CONSTANTS::ADD_BOT_TO_GROUP);
            throw new UnknownChatException($error, __METHOD__);
        }
        return $this;
    }

    protected function setGroupsTitles(): static
    {
        if (empty($this->admin)) {
            throw new BaseTelegramBotException(CONSTANTS::USER_NOT_ALLOWED, __METHOD__);
        }
        $this->groupsTitles = $this->admin->chats->pluck('chat_title')->toArray();
        return $this;
    }

    public function getGroupsTitles(): array
    {
        return $this->groupsTitles;
    }

    protected function findSelectedChatId(): int
    {
        $cleanedCommand = substr($this->command, 1);
        $selectedChat = array_filter($this->groupsTitles, function ($value) use ($cleanedCommand) {
            return $value === $cleanedCommand;
        });

        if (empty($selectedChat)) {
            throw new BaseTelegramBotException(CONSTANTS::SELECTED_CHAT_NOT_SET, __METHOD__);
        }

        $chatId = $this->admin->chats->where('chat_title', $selectedChat[0])->first()->chat_id;
        return $chatId;
    }

    private function chatSelectionHandler()
    {
        if ($this->admin->chats->count() <= 1) {
            $this->botService->setChat($this->admin->chats->first()->chatId);
            return;
        }

        if ($this->checkIfIsSelectChatCommand()) {
            // means that chat was previously selected or that user has entered the select certain chat command manually
            $this->setSelectedChat();
            $lastCommand = $this->getLastCommandFromCache();
            if ($lastCommand) {
                $this->command = $lastCommand;
                return;
            }
        }

        $chatId = $this->getLastSelectedChatIdFromCache();
        if (!empty($chatId)) {
            $this->botService->setChat($chatId);
            return;
        } else {
            $this->sendSelectChatButtons();
            $this->rememberLastCommand();
        }
    }

    private function getLastSelectedChatIdFromCache()
    {
        return Cache::get("last_selected_chat_" . $this->requestModel->getChatId());
    }

    private function rememberLastSelectedChatId()
    {
        return Cache::put(
            "last_selected_chat_" . $this->requestModel->getChatId(),
            $this->botService->getChat()->chat_id
        );
    }

    protected function checkIfIsSelectChatCommand(): bool
    {
        //Remove "/" to compare
        $cleanedCommand = substr($this->command, 1);
        return in_array($cleanedCommand, $this->groupsTitles);
    }

    public function rememberLastCommand()
    {
        Cache::put("last_command_" . $this->requestModel->getChatId(), $this->command);
    }

    private function getLastCommandFromCache()
    {
        return Cache::get("last_command_" . $this->requestModel->getChatId());
    }
}