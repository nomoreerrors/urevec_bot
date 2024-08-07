<?php

namespace App\Enums;

enum Time: int
{
    case HOUR = 3600;
    case DAY = 86400;
    case WEEK = 604800;
    case MONTH = 2592000;
    case YEAR = 31536000;


    public function toString(): string
    {
        return match ($this) {
            self::HOUR => "HOUR",
            self::DAY => "DAY",
            self::WEEK => "WEEK",
            self::MONTH => "MONTH",
            self::YEAR => "YEAR",
        };
    }
}
