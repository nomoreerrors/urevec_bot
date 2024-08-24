<?php

namespace App\Classes;

use App\Classes\CommandChatSelector;
use App\Classes\BadWordsFilterCommand;
use App\Enums\BadWordsFilterEnum;
use App\Classes\ModerationSettingsCommand;
use App\Enums\ModerationSettingsEnum;
use App\Enums\NewUserRestrictionsEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use App\Models\Chat;
use App\Classes\ModerationSettings;
use App\Classes\BaseCommand;
use App\Classes\ReplyKeyboardMarkup;
use App\Classes\NewUserRestrictionsCommand;
use App\Exceptions\BaseTelegramBotException;
use App\Exceptions\UnknownChatException;
use App\Models\Admin;
use App\Models\MessageModels\TextMessageModel;
use App\Models\TelegramRequestModelBuilder;
use App\Services\TelegramBotService;
use App\Traits\BackMenuButton;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Enums\COMMAND;

class PrivateChatCommandCore extends BaseBotCommandCore
{
    public function __construct(protected TelegramBotService $botService)
    {
        parent::__construct($botService);
        $this->command = $this->botService->getPrivateChatCommand();
    }


    public function handle(): void
    {
        $this->botService->chatSelector()->select();
        if (
            $this->botService->chatSelector()->hasBeenUpdated() ||
            $this->botService->chatSelector()->buttonsHaveBeenSent()
        ) {
            return;
        }

        $this->updateCommandIfChanged();

        if (ModerationSettingsEnum::exists($this->command)) {
            new ModerationSettingsCommand($this->botService);
            return;
        }

        if (NewUserRestrictionsEnum::exists($this->command)) {
            new NewUserRestrictionsCommand($this->botService);
            return;
        }

        if (BadWordsFilterEnum::exists($this->command)) {
            new BadWordsFilterCommand($this->botService);
            return;
        }

        if (UnusualCharsFilterEnum::exists($this->command)) {
            new UnusualCharsFilterCommand($this->botService);
            return;
        }

        response(CONSTANTS::UNKNOWN_CMD, 200);
    }


    protected function checkUserAccess(): static
    {
        $admin = $this->botService->getAdmin();

        if (empty($admin)) {
            $error = CONSTANTS::USER_NOT_ALLOWED . " " . $admin->admin_id;
            log::info($error);

            $this->botService->sendMessage(CONSTANTS::ADD_BOT_TO_GROUP);
            throw new UnknownChatException($error, __METHOD__);
        }
        return $this;
    }

    // protected function setMenu()
    // {
    //     // app()->singleton(PrivateChatCommandCore::class, fn() => new PrivateChatCommandCore());
    //     app()->singleton(PrivateChatCommandCore::class, function ($app) {
    //         return new PrivateChatCommandCore();
    //     });

    //     app()->singleton(Menu::class, function ($app) {
    //         return new Menu($this->botService, app(PrivateChatCommandCore::class));
    //     });
    //     $this->menu = app(Menu::class);
    // }

    /**
     * Update the command if it has been resotred from cache after a chat was selected
     * @return void
     */
    protected function updateCommandIfChanged()
    {
        $this->command = $this->botService->getPrivateChatCommand();
    }

}