<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\MainMenuCmd;
use App\Traits\BackMenuButton;

class MainMenuCommand extends BaseCommand
{
    use BackMenuButton;

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
                $this->Back();
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
        $this->rememberBackMenu();
        app("botService")->sendMessage(MainMenuCmd::MODERATION_SETTINGS->replyMessage(), $keyBoard);
    }        //

    public function sendFiltersMainSettings(): void
    {
        $keyBoard = (new Buttons())->getFiltersMainSettingsButtons();
        app("botService")->sendMessage(MainMenuCmd::FILTERS_SETTINGS->replyMessage(), $keyBoard);
    }

    /**
     * Move back to previous menu
     * Remove last element from back menu array in cache
     * and set it as private chat command 
     * @return void
     */
    public function Back(): void
    {
        $this->botService->setPrivateChatCommand($this->getBackMenuFromCache());
        $this->moveUpBackMenuPointer();
        new PrivateChatCommandCore();
    }
}


