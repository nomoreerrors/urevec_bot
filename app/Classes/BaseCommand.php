<?php

namespace App\Classes;

use App\Interfaces\CommandEnumInterface;
use App\Models\Chat;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;
use App\Enums\MainMenuCmd;
use Illuminate\Support\Facades\Cache;

abstract class BaseCommand
{
    protected TelegramBotService $botService;
    protected Chat $chat;

    public function __construct(protected string $command, protected string $enum)
    {
        $this->botService = app("botService");
        $this->chat = $this->botService->getChat();
        $this->handle();        //
    }

    abstract protected function handle();

    abstract public function send(): void;

}