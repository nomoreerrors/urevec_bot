<?php

namespace App\Enums\Traits;

trait GetValues
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
