<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
use App\Enums\Traits\GetValues;

/**
 * Represents menu with all available filters
 */
enum FiltersSettingsEnum: string
{
    use EnumValues;

    case BADWORDS_FILTER_SETTINGS = 'Фильтр запрещенных слов';
    case UNUSUAL_CHARS_FILTER_SETTINGS = 'Фильтр подозрительных символов';

    public function replyMessage(): string
    {
        return match ($this) {
            self::BADWORDS_FILTER_SETTINGS => 'Фильтр запрещенных слов',
            self::UNUSUAL_CHARS_FILTER_SETTINGS => 'Фильтр подозрительных символов',
        };
    }


}
