<?php

namespace App\Enums;

trait RestrictionsCases
{
    public static function getRestrictionsTimeCases(): array
    {
        return [
            self::SELECT_RESTRICTION_TIME,
            self::SET_TIME_TWO_HOURS,
            self::SET_TIME_DAY,
            self::SET_TIME_WEEK,
            self::SET_TIME_MONTH
        ];
    }

    public static function getRestrictionsTimeCasesValues(): array
    {
        return [
            self::SELECT_RESTRICTION_TIME->value,
            self::SET_TIME_TWO_HOURS->value,
            self::SET_TIME_DAY->value,
            self::SET_TIME_WEEK->value,
            self::SET_TIME_MONTH->value
        ];
    }


    public static function getRestrictionsCases(): array
    {
        return [
            self::CAN_SEND_MESSAGES_DISABLE,
            self::CAN_SEND_MESSAGES_ENABLE,
            self::CAN_SEND_MEDIA_DISABLE,
            self::CAN_SEND_MEDIA_ENABLE,
            self::RESTRICT_USER_DISABLE,
            self::RESTRICT_USER_ENABLE,
            self::DELETE_USER_ENABLE,
            self::DELETE_USER_DISABLE
        ];
    }

    public static function getRestrictionsCasesValues(): array
    {
        return [
            self::CAN_SEND_MESSAGES_DISABLE->value,
            self::CAN_SEND_MESSAGES_ENABLE->value,
            self::CAN_SEND_MEDIA_DISABLE->value,
            self::CAN_SEND_MEDIA_ENABLE->value,
            self::RESTRICT_USER_DISABLE->value,
            self::RESTRICT_USER_ENABLE->value,
            self::DELETE_USER_ENABLE->value,
            self::DELETE_USER_DISABLE->value
        ];
    }



}
