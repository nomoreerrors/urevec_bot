<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
use App\Enums\Traits\GetValues;
use App\Enums\RestrictionsCases;
use App\Enums\EnumHasRestrictionTimeInterface;

/**
 * Users restrictions commands
 */
enum NewUserRestrictionsEnum: string implements EnumHasRestrictionTimeInterface
{
    use EnumValues;
    use RestrictionsCases;


    // case SETTINGS = "Настройки ограничений для новых пользователей";
    //Main settings
    case ENABLED_DISABLE = "Отключить все ограничения для новых пользователей";
    case ENABLED_ENABLE = "Включить все ограничения для новых пользователей";
    case EDIT_RESTRICTIONS = "Выбрать ограничения новых пользователей";

    //Restrictions
    case CAN_SEND_MEDIA_ENABLE = "Разрешить медиа-сообщения для новых пользователей";
    case CAN_SEND_MEDIA_DISABLE = "Запретить медиа-сообщения для новых пользователей";
    case CAN_SEND_MESSAGES_ENABLE = "Разрешить отправку сообщений для новых пользователей";
    case CAN_SEND_MESSAGES_DISABLE = "Запретить отправку сообщений для новых пользователей";

    // Restriction time
    case SELECT_RESTRICTION_TIME = "Выбрать время ограничения для новых пользователей";
    case SET_TIME_TWO_HOURS = "Ограничивать новых пользователей на 2 часа";
    case SET_TIME_DAY = "Ограничивать новых пользователей на 24 часа";
    case SET_TIME_WEEK = "Ограничивать новых пользователей на неделю";
    case SET_TIME_MONTH = "Ограничивать новых пользователей на месяц";


    public static function getMainMenuCases(): array
    {
        return [
            self::ENABLED_DISABLE,
            self::ENABLED_ENABLE,
            self::EDIT_RESTRICTIONS
        ];
    }

    public static function getRestrictionsCases(): array
    {
        return [
            self::CAN_SEND_MESSAGES_DISABLE,
            self::CAN_SEND_MESSAGES_ENABLE,
            self::CAN_SEND_MEDIA_DISABLE,
            self::CAN_SEND_MEDIA_ENABLE,
            // self::RESTRICTIONS_DISABLE,
            // self::RESTRICTIONS_ENABLE
        ];
    }

    public function replyMessage()
    {
        return match ($this) {
                // self::SETTINGS => 'Выберите ограничения для новых пользователей',
            self::CAN_SEND_MEDIA_ENABLE => 'Возможность отправки медиа-сообщений для новых участников включена',
            self::CAN_SEND_MEDIA_DISABLE => 'Возможность отправки медиа-сообщений для новых участников отключена',
            self::CAN_SEND_MESSAGES_ENABLE => 'Возможность отправки сообщений для новых участников включена',
            self::CAN_SEND_MESSAGES_DISABLE => 'Возможность отправки сообщений для новых участников отключена',
            self::ENABLED_DISABLE => 'Все ограничения для новых участников отключены',
            self::ENABLED_ENABLE => 'Все ограничения для новых участников включены',
            self::SELECT_RESTRICTION_TIME => 'Выберите время ограничения для новых пользователей',
            self::SET_TIME_TWO_HOURS => 'Установлено ограничение новых пользователей на 2 часа',
            self::SET_TIME_DAY => 'Установлено ограничение новых пользователей на 24 часа',
            self::SET_TIME_WEEK => 'Установлено ограничение новых пользователей на неделю',
            self::SET_TIME_MONTH => 'Установлено ограничение новых пользователей на месяц',
            self::EDIT_RESTRICTIONS => 'Выберите ограничения для новых пользователей',
        };
    }
}



// ENGLISH
// case SETTINGS = "/new_users_restrictions_settings";
// case ENABLE_SEND_MEDIA = "/new_users_restrictions_send_media_enabled";
// case DISABLE_SEND_MEDIA = "/new_users_restrictions_send_media_disabled";
// case ENABLE_SEND_MESSAGES = "/new_users_restrictions_send_messages_enabled";
// case DISABLE_SEND_MESSAGES = "/new_users_restrictions_send_messages_disabled";
// case DISABLE_ALL_RESTRICTIONS = "/new_users_restrictions_disable_all";
// case ENABLE_ALL_RESTRICTIONS = "/new_users_restrictions_enable_all";