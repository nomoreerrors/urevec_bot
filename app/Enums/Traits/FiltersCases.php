<?php

namespace App\Enums\Traits;
use App\Enums\RestrictionsCases;

trait FiltersCases
{
    use RestrictionsCases;

    public static function getFiltersCases(): array
    {
        return [
            self::ENABLED_ENABLE,
            self::ENABLED_DISABLE,
            self::DELETE_MESSAGE_ENABLE,
            self::DELETE_MESSAGE_DISABLE,
            self::EDIT_RESTRICTIONS,
        ];
    }

    public static function getFiltersCasesValues(): array
    {
        return [
            self::ENABLED_ENABLE->value,
            self::ENABLED_DISABLE->value,
            self::DELETE_MESSAGE_ENABLE->value,
            self::DELETE_MESSAGE_DISABLE->value,
            self::EDIT_RESTRICTIONS->value,
        ];
    }
}
