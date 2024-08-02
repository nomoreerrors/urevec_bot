<?php

namespace App\Services;

use App\Classes\ModerationSettings;
use App\Classes\ReplyInterface;
use App\Classes\ReplyKeyboardMarkup;
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

class PrivateChatCommandService extends BotCommandService
{
    private $admin;

    private array $groupsTitles = [];

    protected TextMessageModel $requestModel;

    public function __construct()
    {
        parent::__construct();
        $this->requestModel = app("requestModel");
        $this->admin = Admin::where('admin_id', $this->requestModel->getFromId())->first();
        $this->settings = new ModerationSettings();
        $this->checkUserAccess()
            ->setGroupsTitles()
            ->handle();
    }

    protected function handle(): static
    {
        $this->chatSelectionHandler();
        switch ($this->command) {
            case "/start":
                $this->startHandler();
            case CONSTANTS::MODERATION_SETTINGS_CMD:
                $this->settingsHandler();
                break;
            case CONSTANTS::NEW_USERS_RESTRICT_SETTINGS_CMD:
                $this->settings->sendNewUsersRestrictionsSettings();
                break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD:
            //     $this->cacheRestriction(CONSTANTS::HOUR * 2);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD:
            //     $this->cacheRestriction(CONSTANTS::DAY);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD:
            //     $this->cacheRestriction(CONSTANTS::WEEK);
            //     break;
            // case CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD:
            //     $this->cacheRestriction(CONSTANTS::MONTH);
            //     break;
            // case CONSTANTS::STOP_RESTRICT_NEW_MEMBERS_CMD:
            //     $this->cacheRestriction(0);
            //     break;
            // case CONSTANTS::FILTER_SETTINGS_CMD:
            //     // Handle filter settings command
            //     break;
            // case CONSTANTS::BAN_SETTINGS_CMD:
            //     // Handle ban settings command
            //     break;
            default:
                app("botService")->sendMessage("Неизвестная команда");
                log::info("Неизвестная команда в приватном чате" . $this->command);
                response(CONSTANTS::UNKNOWN_CMD, 200);
        }
        return $this;
    }

    /**
     * Send the list of available chats to user as buttons
     * @return void
     */
    private function setChat(): void
    {
        $chatId = $this->findSelectedChatId();
        $this->botService->setChat($chatId);
        $this->chat = $this->botService->getChat();
        $this->botService->sendMessage("Selected chat: " . $this->chat->chat_title);
    }

    public function sendSelectChatButtons(): void
    {
        $keyBoard = $this->buttons->getSelectChatButtons($this->groupsTitles);
        app("botService")->sendMessage("Select chat", $keyBoard);
        return;
    }

    private function startHandler(): Response
    {
        $this->settings->send();
        return response();
    }

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

    protected function settingsHandler(): void
    {
        if (!empty($this->botService->getChat())) {
            $this->settings->send();
        } else {
            $this->setChat();
        }
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

    protected function checkIfIsSelectChatCommand(): bool
    {
        //Remove "/" to compare
        $cleanedCommand = substr($this->command, 1);
        return in_array($cleanedCommand, $this->groupsTitles);
    }

    public function rememberCommand()
    {
        Cache::put("last_command_" . $this->requestModel->getChatId(), $this->command);
    }

    /**
     * Get the previously saved command from cache and execute it, than update chat
     * @return PrivateChatCommandService
     */
    private function chatSelectionHandler(): static
    {
        if ($this->checkIfIsSelectChatCommand()) {
            // means that chat was previously selected or that user has entered the select certain chat command manually
            $this->setChat();
            $lastCommand = Cache::get(CONSTANTS::CACHE_LAST_COMMAND . $this->requestModel->getChatId());
            if ($lastCommand) {
                $this->command = $lastCommand;
            }
        }
        // if chat not selected yet but the user is sending any command, cache it to
        //  use after selecting chat and send select chat buttons
        if (empty($this->chat)) {
            $this->sendSelectChatButtons();
            $this->rememberCommand();
        }

        return $this;
    }
}




