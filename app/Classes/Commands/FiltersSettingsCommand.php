<?php

namespace App\Classes\Commands;

use App\Classes\Buttons;
use App\Enums\CommandEnums\LinksFilterEnum;
use App\Enums\NewUserRestrictionsEnum;
use App\Models\Chat;
use App\Enums\ModerationSettingsEnum;
use App\Services\TelegramBotService;
use App\Classes\Menu;
use App\Enums\MainMenuEnum;
use App\Services\BotErrorNotificationService;
use App\Enums\CommandEnums\FiltersSettingsEnum;
use App\Enums\CommandEnums\BadWordsFilterEnum;
use App\Enums\CommandEnums\UnusualCharsFilterEnum;

class FiltersSettingsCommand extends BaseCommand
{
    public function __construct(protected TelegramBotService $botService)
    {
        parent::__construct($botService);
    }

    protected function handle(): void
    {
        switch ($this->command) {
            case $this->enum::BADWORDS_FILTER_SETTINGS->value:
                $this->sendBadWordsFilterSettings();
                break;
            case $this->enum::UNUSUAL_CHARS_FILTER_SETTINGS->value:
                $this->sendUnusualCharsFilterSettings();
                break;
            case $this->enum::LINKS_FILTER_SETTINGS->value:
                $this->sendLinksFilterSettings();
                break;
        }
    }

    protected function sendBadWordsFilterSettings(): void
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getBadWordsFilterButtons($this->botService->getChat()->badWordsFilter, BadWordsFilterEnum::class);
        $this->botService->sendMessage(FiltersSettingsEnum::BADWORDS_FILTER_SETTINGS->replyMessage(), $keyBoard);
    }


    protected function sendUnusualCharsFilterSettings(): void
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getUnusualCharsFilterButtons($this->botService->getChat()->unusualCharsFilter, UnusualCharsFilterEnum::class);

        $this->botService->sendMessage(FiltersSettingsEnum::UNUSUAL_CHARS_FILTER_SETTINGS->replyMessage(), $keyBoard);
    }


    protected function sendLinksFilterSettings(): void
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getLinksFilterButtons($this->botService->getChat()->linksFilter, LinksFilterEnum::class);
        $this->botService->sendMessage(FiltersSettingsEnum::LINKS_FILTER_SETTINGS->replyMessage(), $keyBoard);
    }
}



