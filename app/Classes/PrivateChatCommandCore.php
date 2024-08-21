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
use App\Services\TelegramBotService;
use App\Traits\BackMenuButton;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Enums\COMMAND;

class PrivateChatCommandCore extends BaseBotCommandCore
{
    private ChatSelector $chatSelector;

    private Menu $menu;

    public function __construct()
    {
        parent::__construct();
        $this->botService = app(TelegramBotService::class);
        $this->command = $this->botService->getPrivateChatCommand();
        $this->admin = $this->botService->getAdmin();
        $this->checkUserAccess();
        $this->requestModel = $this->botService->getRequestModel();
        $this->chatSelector = new ChatSelector($this->botService, app(Menu::class));
    }


    public function handle(): void
    {
        if ($this->chatSelector->buttonsHaveBeenSent() || $this->chatSelector->hasBeenUpdated()) {
            return;
        }

        $this->updateCommandIfChanged();
        $chat = $this->botService->getChat();

        if (ModerationSettingsEnum::exists($this->command)) {
            new ModerationSettingsCommand($this->command, ModerationSettingsEnum::class);
            return;
        }

        if (ResNewUsersEnum::exists($this->command)) {
            new RestrictNewUsersCommand($this->command, $chat->newUserRestrictions, ResNewUsersEnum::class);
            return;
        }

        if (BadWordsFilterEnum::exists($this->command)) {
            new BadWordsFilterCommand($this->command, $chat->badWordsFilter, BadWordsFilterEnum::class);
            return;
        }

        if (UnusualCharsFilterEnum::exists($this->command)) {
            new UnusualCharsFilterCommand($this->command, $chat->unusualCharsFilter, UnusualCharsFilterEnum::class);
            return;
        }

        response(CONSTANTS::UNKNOWN_CMD, 200);
    }


    protected function checkUserAccess(): static
    {
        if (empty($this->admin)) {
            $error = CONSTANTS::USER_NOT_ALLOWED . " " . $this->admin->admin_id;
            log::info($error);

            app("botService")->sendMessage(CONSTANTS::ADD_BOT_TO_GROUP);
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