<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\ResTime;
use App\Enums\BadWordsFilterEnum;
use App\Services\TelegramBotService;
use PHPUnit\Util\Filter;
use App\Classes\Menu;
use App\Models\FilterModel;

class BadWordsFilterCommand extends FilterCommand
{
    protected function handle(): void
    {
        parent::handle();
        switch ($this->command) {
            //ADD SPECIFIC COMMAND CASES
        }
    }

}
