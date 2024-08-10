<?php

namespace App\Classes;

use App\Models\Admin;
use App\Services\CONSTANTS;
use App\Models\MessageModels\TextMessageModel;
use App\Exceptions\BaseTelegramBotException;
use App\Services\TelegramBotService;
use App\Classes\Buttons;
use Illuminate\Support\Facades\Cache;

class ChatSelector
{
    private TelegramBotService $botService;

    private TextMessageModel $requestModel;

    private $admin = null;

    private Buttons $buttons;

    private bool $buttonsSended = false;

    private array $groupsTitles = [];

    /**
     * Selecting an active chat to work with in bot private chat if an admin attached to multiple chats in database
     */
    public function __construct()
    {
        $this->botService = app("botService");
        $this->requestModel = app("requestModel");
        $this->admin = $this->botService->getAdmin();
        $this->buttons = new Buttons();
        $this->setGroupsTitles();
        $this->select();
    }

    private function select()
    {
        if ($this->hasOnlyOneChat())
            return $this->setDefaultChat();

        if ($this->isSelectChatCommand()) {
            $this->setSelectedChatAndNotice();
            $this->restorePreviousCommandIfExists();
            return;

        } elseif (!$this->tryToGetLastChatFromCache()) {
            $this->sendSelectChatButtons();
            $this->rememberLastCommand();
        }
    }

    public function getLastSelectedChatIdFromCache()
    {
        return Cache::get("last_selected_chat_" . $this->requestModel->getChatId());
    }

    /**
     * Save last selected chat id in cache with an admin id as a cache key postfix
     * @return bool
     */
    public function rememberSelectedChatId()
    {
        return Cache::put(
            "last_selected_chat_" . $this->requestModel->getChatId(),
            $this->botService->getChat()->chat_id
        );
    }

    /**
     * Checks if the command is a title of one of the user's chats
     * which means that a select certain chat button was pressed by user 
     * @return bool
     */
    public function isSelectChatCommand(): bool
    {
        return in_array($this->botService->getPrivateChatCommand(), $this->groupsTitles);
    }

    public function rememberLastCommand(): void
    {
        Cache::put("last_command_" . $this->requestModel->getChatId(), $this->botService->getPrivateChatCommand());
    }

    public function getLastCommandFromCache()
    {
        return Cache::get("last_command_" . $this->requestModel->getChatId());
    }


    private function setGroupsTitles(): static
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

    private function findSelectedChatId(): int
    {
        $selectedChat = array_filter($this->groupsTitles, function ($value) {
            return $value === $this->botService->getPrivateChatCommand();
        });

        if (empty($selectedChat)) {
            throw new BaseTelegramBotException(CONSTANTS::SELECTED_CHAT_NOT_SET, __METHOD__);
        }

        $chatId = $this->admin->chats->where('chat_title', $selectedChat[0])->first()->chat_id;
        return $chatId;
    }

    /**
     * Set the chat that was selected by the user as an active chat and send a message to the user
     * about which chat was selected
     * @return void
     */
    public function setSelectedChatAndNotice(): void
    {
        $chatId = $this->findSelectedChatId();
        $this->botService->setChat($chatId);
        $this->botService->sendMessage("Selected chat: " . $this->botService->getChat()->chat_title);
        $this->rememberSelectedChatId();
    }

    public function sendSelectChatButtons(): void
    {
        $keyBoard = $this->buttons->getSelectChatButtons($this->groupsTitles);
        app("botService")->sendMessage("Select chat", $keyBoard);
        $this->buttonsSended = true;
        return;
    }

    public function hasOnlyOneChat(): bool
    {
        return $this->admin->chats->count() > 1 ? false : true;
    }

    public function selectChatButtonsSended(): bool
    {
        return $this->buttonsSended;
    }

    private function tryToGetLastChatFromCache(): bool
    {
        if (!empty($this->getLastSelectedChatIdFromCache())) {
            $this->botService->setChat($this->getLastSelectedChatIdFromCache());
            return true;
        }
        return false;
    }

    private function restorePreviousCommandIfExists()
    {
        $lastCommand = $this->getLastCommandFromCache();
        if ($lastCommand) {
            $this->botService->setPrivateChatCommand($lastCommand);
            return;
        }
    }

    private function setDefaultChat(): void
    {
        $this->botService->setChat($this->admin->chats->first()->chat_id);
    }

}

