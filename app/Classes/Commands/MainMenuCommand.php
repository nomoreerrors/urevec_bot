<?php

namespace App\Classes\Commands;

use App\Enums\BadWordsFilterEnum;
use App\Enums\NewUserRestrictionsEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Models\Chat;
use App\Enums\ModerationSettingsEnum;
use App\Services\TelegramBotService;
use App\Classes\Menu;
use App\Enums\MainMenuEnum;
use App\Services\BotErrorNotificationService;

class MainMenuCommand extends BaseCommand
{
    public function __construct(protected TelegramBotService $botService)
    {
        parent::__construct($botService);
    }

    protected function handle(): void
    {
        switch ($this->command) {
            case $this->enum::MODERATION_SETTINGS->value:
                $this->sendModerationSettings();
                break;
            case $this->enum::FILTERS_SETTINGS->value:
                $this->sendFiltersMainSettings();
                break;
            // case $this->enum::SELECT_CHAT->value:

            case $this->enum::BACK->value:
                $this->botService->menu()->back();
                break;
        }
    }

    public function sendFiltersMainSettings(): void
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getFiltersSettingsButtons();
        $this->botService->sendMessage(ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage(), $keyBoard);
    }


    public function sendModerationSettings(): void
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->create(ModerationSettingsEnum::getValues(), 1, true);
        $this->botService->sendMessage(MainMenuEnum::MODERATION_SETTINGS->replyMessage(), $keyBoard);
    }


    protected function getSettingsButtons(): array
    {
        $keyBoard = (new Buttons())->getModerationSettingsButtons();
        return $keyBoard;
    }
}


