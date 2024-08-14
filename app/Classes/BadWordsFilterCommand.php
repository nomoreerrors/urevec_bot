<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\ResTime;
use App\Enums\BadWordsFilterEnum;
use App\Services\TelegramBotService;
use PHPUnit\Util\Filter;
use App\Classes\BackMenuButton;
use App\Models\FilterModel;

class BadWordsFilterCommand extends FilterCommand
{
    protected function handle(): void
    {
        parent::handle();
        switch ($this->command) {
            //ADD SPECIFIC CASES
        }
    }

    protected function getSettingsTitles(): array
    {
        $titles = parent::getSettingsTitles();
        $addTitles = [
            $this->enum::ADD_WORDS->value,
            $this->enum::DELETE_WORDS->value,
            $this->enum::GET_WORDS->value,
        ];

        return array_merge($titles, $addTitles);
    }
}
