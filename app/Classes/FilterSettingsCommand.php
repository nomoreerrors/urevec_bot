<?php

namespace App\Classes;

use App\Models\Chat;
use App\Services\TelegramBotService;

class FilterSettingsCommand implements ReplyInterface
{
    private TelegramBotService $botService;
    public function __construct(private string $command)
    {
        $this->botService = app("botService");
    }
    public function send(): void
    {
        //
    }
}
