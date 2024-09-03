<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;

enum MainMenuEnum: string
{
    use EnumValues;

    case MODERATION_SETTINGS = '/moderation_settings';
    case FILTERS_SETTINGS = 'Настройки фильтров сообщений';
    // case SELECT_CHAT = 'Выбрать чат';
    case BACK = 'Назад';

    public function replyMessage(): string
    {
        return match ($this) {
            self::MODERATION_SETTINGS => 'Настройки модерации чата',
        // self::SELECT_CHAT => 'Выберите чат, который хотите настроить',
        };
    }
}
