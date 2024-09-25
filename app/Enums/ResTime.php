<?php

namespace App\Enums;

use App\Exceptions\BaseTelegramBotException;
use App\Enums\Time;
use App\Services\BotErrorNotificationService;

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
            self::TWO_HOURS => "RESTRICT TIME: TWO HOURS",
            self::DAY => "RESTRICT TIME: DAY",
            self::WEEK => "RESTRICT TIME: ONE WEEK",
            self::MONTH => "RESTRICT TIME: ONE MONTH",
            self::NONE => "RESTRICT TIME: NONE"
        };
    }

    public function getSeconds(): string
    {
        return match ($this) {
            self::TWO_HOURS => Time::HOUR->value * 2,
            self::DAY => Time::DAY->value,
            self::WEEK => Time::WEEK->value,
            self::MONTH => Time::MONTH->value,
        };
    }

    /**
     * Summary of getTime
     * @param EnumHasRestrictionTimeInterface $enumCase
     * @return void
     */
    public static function getTime(EnumHasRestrictionTimeInterface $enumCase): int
    {
        return match ($enumCase->name) {
            'SET_TIME_TWO_HOURS' => self::TWO_HOURS->value,
            'SET_TIME_DAY' => self::DAY->value,
            'SET_TIME_WEEK' => self::WEEK->value,
            'SET_TIME_MONTH' => self::MONTH->value,
            default => throw new BaseTelegramBotException("Not found restriction time: " . $enumCase->name, __METHOD__)
        };
    }

    public function getRussianReply(): string
    {
        return match ($this) {
            self::TWO_HOURS => "на 2 часа",
            self::DAY => "на 24 часа",
            self::WEEK => "на неделю",
            self::MONTH => "на месяц",
            self::NONE => "0",
        };
    }
}
