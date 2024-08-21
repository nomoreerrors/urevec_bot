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
    public function __construct(private TelegramBotService $botService, private Menu $menu)
    {
        $this->requestModel = $this->botService->getRequestModel();
        $this->admin = $this->botService->getAdmin();
        $this->buttons = new Buttons();
        $this->command = $this->botService->getPrivateChatCommand();
        $this->setGroupsTitles();
        $this->select();
    }

    private function select()
    {
        if ($this->hasOnlyOneChat()) {
            $this->setDefaultChat();
            return;
        }

        if ($this->isSelectChatMenuRequest()) {
            $this->sendSelectChatButtons();
            return;
        }

        if ($this->isSelectedChatCommand()) {
            $this->setSelectedChatAndNotice();
            $this->rememberSelectedChatId();
            $this->restorePreviousCommandIfExists();
            $this->menu->back();
            return;
        }

        if ($this->tryToGetLastChatFromCache()) {
            return;
        }

        $this->sendSelectChatButtons();
        $this->rememberLastCommand();
        return;
    }

    public function getLastSelectedChatIdFromCache()
    {
        $stop = Cache::get("last_selected_chat_" . $this->admin->admin_id);
        return Cache::get("last_selected_chat_" . $this->admin->admin_id);
    }

    /**
     * Save last selected chat id in cache with an admin id as a cache key postfix
     * @return bool
     */
    public function rememberSelectedChatId()
    {
        return Cache::put(
            "last_selected_chat_" . $this->requestModel->getFromId(), //private chat admin's id
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
        Cache::put("last_command_" . $this->admin->admin_id, $this->botService->getPrivateChatCommand());
    }

    public function getLastCommandFromCache()
    {
        $o = $this->requestModel->getChatId();
        $j = Cache::get("last_command_" . $this->requestModel->getChatId());
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
     */
    public function setSelectedChatAndNotice(): void
    {
        $selectedChatId = $this->admin
            ->chats()
            ->where('chat_title', $this->command)
            ->first()->chat_id;

        $this->botService->setChat($selectedChatId);
        $this->botService->sendMessage(
            "Selected chat: " . $this->botService->getChat()->chat_title
        );

        $this->updated = true;
    }

    public function sendSelectChatButtons(): void
    {
        $this->menu->save();
        $keyBoard = $this->buttons->create($this->groupsTitles, 1, true);
        $this->botService->sendMessage(ModerationSettingsEnum::SELECT_CHAT->replyMessage(), $keyBoard);
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
        $chatId = $this->getLastSelectedChatIdFromCache();
        if ($chatId) {
            $this->botService->setChat($chatId);
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

    public function hasBeenUpdated(): bool
    {
        return $this->updated;
    }

    public function buttonsHaveBeenSent(): bool
    {
        return $this->buttonsSended;
    }

    private function isSelectChatMenuRequest(): bool
    {
        return $this->command === ModerationSettingsEnum::SELECT_CHAT->value;
    }

}

