<?php

namespace App\Enums;

enum ResTime: int
{
    case TWO_HOURS = 1;
    case DAY = 2;
    case WEEK = 3;
    case MONTH = 4;
    case NONE = 0;

    public function getHumanRedable(): string
    {
        return match ($this) {
            ResTime::TWO_HOURS => "RESTRICT TIME: TWO HOURS",
            ResTime::DAY => "RESTRICT TIME: DAY",
            ResTime::WEEK => "RESTRICT TIME: ONE WEEK",
            ResTime::MONTH => "RESTRICT TIME: ONE MONTH",
            ResTime::NONE => "RESTRICT TIME: NONE"
        };
    }
}
