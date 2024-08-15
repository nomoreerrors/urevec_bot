<?php

namespace App\Classes;

use App\Enums\ModerationSettingsEnum;
use App\Models\Admin;
use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use App\Models\MessageModels\TextMessageModel;
use App\Exceptions\BaseTelegramBotException;
use App\Services\TelegramBotService;
use App\Classes\Buttons;
use Illuminate\Support\Facades\Cache;

class ChatSelector
{
    private TelegramBotService $botService;

    private $requestModel;

    private $admin = null;

    private Buttons $buttons;

    private bool $updated = false;

    private string $command;

    private bool $buttonsSended = false;

    private array $groupsTitles = [];

    /**
     * Selecting an active chat to work with in bot private chat if an admin attached to multiple chats in database
     */
    public function __construct()
    {
        $this->botService = app("botService");
        $this->requestModel = $this->botService->getRequestModel();
        $this->admin = $this->botService->getAdmin();
        $this->buttons = new Buttons();
        $this->command = $this->botService->getPrivateChatCommand();
        $this->setGroupsTitles();
        $this->select();
    }

    private function select()
    {
        if ($this->hasOnlyOneChat())
            return $this->setDefaultChat();


        if ($this->command === ModerationSettingsEnum::SELECT_CHAT->value) {
            $this->sendSelectChatButtons();
            return;
        }

        if ($this->isSelectedChatCommand()) {
            $this->setSelectedChatAndNotice();
            $this->rememberSelectedChatId();
            $this->restorePreviousCommandIfExists();
            return;

        } elseif (!$this->tryToGetLastChatFromCache()) {
            $this->sendSelectChatButtons();
            $this->rememberLastCommand();
            return;
        }
        return;
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
    public function isSelectedChatCommand(): bool
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

    /**
     * Set the chat that was selected by the user as an active chat and send a message to the user
     * about which chat was selected
     * @return void
     */
    public function setSelectedChatAndNotice(): void
    {
        $id = $this->admin->chats->where('chat_title', $this->command)->first()->chat_id;
        $this->botService->setChat($id);
        $this->botService->sendMessage("Selected chat: " . $this->botService->getChat()->chat_title);
        $this->updated = true;
        Menu::back();
    }

    public function sendSelectChatButtons(): void
    {
        Menu::save(ModerationSettingsEnum::SELECT_CHAT->value);
        $keyBoard = $this->buttons->createButtons($this->groupsTitles, 1, true);
        app("botService")->sendMessage(ModerationSettingsEnum::SELECT_CHAT->replyMessage(), $keyBoard);
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

    public function updated(): bool
    {
        return $this->updated;
    }

    public function buttonsSended(): bool
    {
        return $this->buttonsSended;
    }

}

