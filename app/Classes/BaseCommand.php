<?php

namespace App\Classes;

use App\Models\Chat;
use App\Services\TelegramBotService;
use App\Enums\MainMenu;

abstract class BaseCommand
{
    protected TelegramBotService $botService;
    protected Chat $chat;

    public function __construct(private string $command)
    {
        $this->botService = app("botService");
        $this->chat = $this->botService->getChat();
        $this->handle();        //
    }

    abstract protected function handle(): static;

    abstract public function send(): void;

}