<?php

namespace App\Classes;

use App\Classes\CommandChatSelector;
use App\Classes\BadWordsFilterCommand;
use App\Enums\BadWordsFilterEnum;
use App\Classes\ModerationSettingsCommand;
use App\Enums\ModerationSettingsEnum;
use App\Enums\ResNewUsersEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Services\BotErrorNotificationService;
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
use App\Traits\BackMenuButton;
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


    protected function handle(): void
    {
        if (
            $this->chatSelector->buttonsSended() ||
            $this->chatSelector->updated()
        ) {
            return;
        }

        if (ModerationSettingsEnum::exists($this->command)) {
            new ModerationSettingsCommand($this->command, ModerationSettingsEnum::class);
            return;
        }

        if (ResNewUsersEnum::exists($this->command)) {
            $restrictions = $this->botService->getChat()->newUserRestrictions;
            new RestrictNewUsersCommand($this->command, $restrictions, ResNewUsersEnum::class);
            return;
        }

        if (BadWordsFilterEnum::exists($this->command)) {
            $filter = $this->botService->getChat()->badWordsFilter;
            new BadWordsFilterCommand($this->command, $filter, BadWordsFilterEnum::class);
            return;
        }

        if (UnusualCharsFilterEnum::exists($this->command)) {
            $filter = $this->botService->getChat()->unusualCharsFilter;
            new UnusualCharsFilterCommand($this->command, $filter, UnusualCharsFilterEnum::class);
            return;
        }

        // BotErrorNotificationService::send("Неизвестная команда в приватном чате" . $this->command);
        app("botService")->sendMessage("Неизвестная команда");
        log::info("Неизвестная команда в приватном чате" . $this->command);
        response(CONSTANTS::UNKNOWN_CMD, 200);
        return;
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