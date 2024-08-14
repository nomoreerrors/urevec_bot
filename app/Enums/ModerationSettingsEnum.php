<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum ModerationSettingsEnum: string
{
    use Exists;

    case MODERATION_SETTINGS = '/moderation_settings';
    case SELECT_CHAT = 'Выбрать чат';
    case FILTERS_SETTINGS = 'Настройки фильтров сообщений';
    case BACK = 'Назад';

    public function replyMessage(): string
    {
        return match ($this) {
            self::MODERATION_SETTINGS => 'Настройки модерации чата',
            self::SELECT_CHAT => 'Выберите чат, который хотите настроить',
            self::FILTERS_SETTINGS => 'Выберите фильтр, который хотите настроить',
        };
    }

    public function withTitle(string $title): string
    {
        return match ($this) {
            self::MODERATION_SETTINGS => $this->replyMessage() . ' - ' . $title,
            self::SELECT_CHAT => $this->replyMessage() . ' - ' . $title,
            self::FILTERS_SETTINGS => 'Выберите фильтр, который хотите настроить',
        };
    }
}
