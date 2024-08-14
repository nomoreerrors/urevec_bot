<?php

namespace App\Classes;

use App\Models\Chat;
use App\Services\TelegramBotService;
use App\Traits\RestrictUsers;
use App\Traits\RestrictionsTimeCases;
use App\Traits\RestrictionsCases;

abstract class BaseCommand
{
    use RestrictionsTimeCases;
    use RestrictionsCases;
    use RestrictUsers;

    protected TelegramBotService $botService;
    protected Chat $chat;

    public function __construct(protected string $command, protected string $enum)
    {
        $this->botService = app("botService");
        $this->chat = $this->botService->getChat();
        $this->handle();        //
    }

    protected function handle(): void
    {
        $this->getBaseMenuCases();
    }

    public function send(): void
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = $this->getSettingsButtons();
        app("botService")->sendMessage($this->enum::SETTINGS->replyMessage(), $keyBoard);
    }


    protected function getSettingsButtons(): array
    {
        return (new Buttons())->createButtons($this->getSettingsTitles(), 1, true);
    }

    protected function getBaseMenuCases()
    {
        switch ($this->command) {
            case $this->enum::SETTINGS->value:
                $this->send();
                break;
        }
    }

    protected abstract function getSettingsTitles(): array;

}
