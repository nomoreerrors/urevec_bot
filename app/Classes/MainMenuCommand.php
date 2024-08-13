<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\MainMenuCmd;
use App\Classes\BackMenuButton;
use App\Services\BotErrorNotificationService;

class MainMenuCommand extends BaseCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($command);
    }

    protected function handle(): static
    {
        switch ($this->command) {
            case MainMenuCmd::MODERATION_SETTINGS->value:
                $this->sendModerationSettings();
                break;
            case MainMenuCmd::FILTERS_SETTINGS->value:
                $this->sendFiltersMainSettings();
                break;
            case MainMenuCmd::BACK->value:
                BackMenuButton::back();
                break;
        }
        return $this;
    }

    public function send(): void
    {
        //FOR START COMMAND
    }

    public function sendModerationSettings(): void
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->getModerationSettingsButtons();
        app("botService")->sendMessage(MainMenuCmd::MODERATION_SETTINGS->replyMessage(), $keyBoard);
    }        //

    public function sendFiltersMainSettings(): void
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->getFiltersMainSettingsButtons();
        app("botService")->sendMessage(MainMenuCmd::FILTERS_SETTINGS->replyMessage(), $keyBoard);
    }
}


