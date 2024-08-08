<?php

namespace App\Classes;

use App\Models\Chat;
use App\Services\TelegramBotService;

class FilterSettingsCommand extends BaseCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($this->command);
    }

    protected function handle(): static
    {
        //
        return $this;
    }
    public function send(): void
    {
        //
    }
}
