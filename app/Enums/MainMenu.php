<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum MainMenu: string
{
    use Exists;

    case SETTINGS = 'Настроить модерацию чата';
    case SELECT_CHAT = 'Выбрать чат';

    public function replyMessage(): string
    {
        return match ($this) {
            self::SETTINGS => 'Настройки модерации чата',
            self::SELECT_CHAT => 'Вы выбрали чат',
        };
    }

    public function withTitle(string $title): string
    {
        return match ($this) {
            self::SETTINGS => $this->replyMessage() . ' - ' . $title,
            self::SELECT_CHAT => $this->replyMessage() . ' - ' . $title,
        };
    }
}
