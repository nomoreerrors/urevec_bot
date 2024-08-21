<?php

namespace App\Classes;

use App\Enums\BadWordsFilterEnum;
use App\Enums\ResNewUsersEnum;
use App\Enums\UnusualCharsFilterEnum;
use App\Models\Chat;
use App\Enums\ModerationSettingsEnum;
use App\Classes\Menu;
use App\Services\BotErrorNotificationService;

class ModerationSettingsCommand extends BaseCommand
{
    public function __construct(protected string $command, protected string $enum)
    {
        parent::__construct($command, $enum);
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
                Menu::back();
                break;
        }
    }

    public function send(): void
    {
        //FOR START COMMAND
    }

    public function sendModerationSettings(): void
    {
        Menu::save($this->command);
        $keyBoard = (new Buttons())->create($this->getSettingsTitles(), 1, false);
        app("botService")->sendMessage(ModerationSettingsEnum::MODERATION_SETTINGS->replyMessage(), $keyBoard);
    }

    public function sendFiltersMainSettings(): void
    {
        Menu::save($this->command);
        $keyBoard = (new Buttons())->create($this->getFiltersSettingsTitles(), 1, true);
        app("botService")->sendMessage(ModerationSettingsEnum::FILTERS_SETTINGS->replyMessage(), $keyBoard);
    }

    protected function getSettingsTitles(): array
    {
        return [
            ResNewUsersEnum::SETTINGS->value,
            $this->enum::FILTERS_SETTINGS->value,
            $this->enum::SELECT_CHAT->value,

        ];
    }

    protected function getFiltersSettingsTitles(): array
    {
        return [
            BadWordsFilterEnum::SETTINGS->value,
            UnusualCharsFilterEnum::SETTINGS->value,
        ];
    }
}


