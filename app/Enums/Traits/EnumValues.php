<?php

namespace App\Enums\Traits;

trait EnumValues
{
    /**
     * Check if value exists in enum
     */
    public static function exists(string $value): bool
    {
        $values = array_column(self::cases(), 'value');
        return in_array($value, $values);
    }

    public static function hasCase(string $value): bool
    {
        $caseNames = array_map(fn($case) => $case->name, self::cases());
        $result = in_array($value, $caseNames);
        return $result;
    }

    /**
     * @return array
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

}
