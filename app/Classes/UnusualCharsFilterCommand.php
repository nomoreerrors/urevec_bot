<?php

namespace App\Classes;
use App\Enums\UnusualCharsFilterEnum;
use App\Models\Chat;
use App\Enums\ResTime;
use App\Enums\BadWordsFilterEnum;
use App\Models\UnusualCharsFilter;
use App\Services\TelegramBotService;
use App\Traits\RestrictUsers;
use PHPUnit\Util\Filter;
use App\Classes\Menu;

class UnusualCharsFilterCommand extends FilterCommand
{
    protected function handle(): void
    {
        parent::handle();
        switch ($this->command) {
            // Additional cases
        }
    }

}
