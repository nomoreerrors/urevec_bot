<?php

namespace App\Enums\Traits;

trait Exists
{
    /**
     * Check if value exists in enum
     */
    public static function exists(string $value): bool
    {
        $values = array_column(self::cases(), 'value');
        return in_array($value, $values);
    }
}
