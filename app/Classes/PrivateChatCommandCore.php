<?php

namespace App\Classes;

use App\Classes\CommandChatSelector;
use App\Classes\FilterSettingsCommand;
use App\Enums\BadWordsFilterCmd;
use App\Classes\MainMenuCommand;
use App\Enums\MainMenu;
use App\Enums\ResNewUsersCmd;
use App\Enums\UnusualCharsFilterCmd;
use App\Services\CONSTANTS;
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
    private ChatSelector $chatSelector;

    // protected TextMessageModel $requestModel;

    public function __construct()
    {
        parent::__construct();
        $this->checkUserAccess();
        $this->chatSelector = new ChatSelector();
        $this->command = $this->botService->getPrivateChatCommand();
        $this->handle();
    }


    protected function handle(): static
    {
        if ($this->chatSelector->selectChatButtonsSended()) {
            return $this;
        }

        if (MainMenu::exists($this->command)) {
            new MainMenuCommand($this->command);
            return $this;
        }


        if (ResNewUsersCmd::exists($this->command)) {
            new RestrictNewUsersCommand($this->command);
            return $this;
        }

        if (
            BadWordsFilterCmd::exists($this->command) ||
            UnusualCharsFilterCmd::exists($this->command)
        ) {
            new FilterSettingsCommand($this->command);
            return $this;
        }


        app("botService")->sendMessage("Неизвестная команда");
        log::info("Неизвестная команда в приватном чате" . $this->command);
        response(CONSTANTS::UNKNOWN_CMD, 200);
        return $this;
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

}