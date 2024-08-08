<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\MainMenu;

class MainMenuCommand extends BaseCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($command);
    }

    protected function handle(): static
    {
        switch ($this->command) {
            // case MainMenu::START
            // $this->send();
            // break;
            case MainMenu::SETTINGS->value:
                $this->sendModerationSettings();
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
        $keyBoard = (new Buttons())->getModerationSettingsButtons();
        app("botService")->sendMessage(MainMenu::SETTINGS->replyMessage(), $keyBoard);
    }        //
}


