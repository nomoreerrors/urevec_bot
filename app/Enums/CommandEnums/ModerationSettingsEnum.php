<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
use App\Enums\Traits\GetValues;

enum ModerationSettingsEnum: string
{
    use EnumValues;
    // use GetValues;

    case SELECT_CHAT = 'Выбрать чат';
    case FILTERS_SETTINGS = 'Настройки фильтров сообщений';
    case RESTRICT_NEW_USERS_SETTINGS = "Настройки ограничений для новых пользователей";

    public function replyMessage(): string
    {
        return match ($this) {
            self::SELECT_CHAT => 'Выберите чат, который хотите настроить',
            self::FILTERS_SETTINGS => 'Выберите фильтр, который хотите настроить',
        };
    }


    public function withTitle(string $title): string
    {
        return match ($this) {
            self::SELECT_CHAT => $this->replyMessage() . ' - ' . $title,
            self::FILTERS_SETTINGS => 'Выберите фильтр, который хотите настроить',
        };
    }
}
