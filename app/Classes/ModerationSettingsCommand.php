<?php

namespace App\Classes;

use App\Enums\BadWordsFilterEnum;
use App\Enums\NewUserRestrictionsEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Models\Chat;
use App\Enums\ModerationSettingsEnum;
use App\Services\TelegramBotService;
use App\Classes\Menu;
use App\Services\BotErrorNotificationService;

class ModerationSettingsCommand extends BaseCommand
{
    public function __construct(protected TelegramBotService $botService)
    {
        parent::__construct($botService);
    }

    protected function handle(): void
    {
        switch ($this->command) {
            case $this->enum::SETTINGS->value:
                $this->send();
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
        // Menu::save($this->command);
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getFiltersMenuSettingsButtons();
        $this->botService->sendMessage(ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage(), $keyBoard);
    }


    protected function setEnum(): void
    {
        $this->enum = ModerationSettingsEnum::class;
    }


    protected function getSettingsButtons(): array
    {
        $keyBoard = (new Buttons())->getModerationSettingsButtons();
        return $keyBoard;
    }
}


