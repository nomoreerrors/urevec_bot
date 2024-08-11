<?php

namespace App\Enums;

use App\Enums\Traits\Exists;
use App\Enums\EnumHasRestrictionTimeInterface;

/**
 * Users restrictions commands
 */
enum ResNewUsersCmd: string implements EnumHasRestrictionTimeInterface
{
    use Exists;

    case SETTINGS = "Настройки ограничений для новых пользователей";
    case ENABLE_SEND_MEDIA = "Разрешить медиа-сообщения для новых пользователей";
    case DISABLE_SEND_MEDIA = "Запретить медиа-сообщения для новых пользователей";
    case ENABLE_SEND_MESSAGES = "Разрешить отправку сообщений для новых пользователей";
    case DISABLE_SEND_MESSAGES = "Запретить отправку сообщений для новых пользователей";
    case DISABLE_ALL = "Отключить все ограничения для новых пользователей";
    case ENABLE_ALL = "Включить все ограничения для новых пользователей";
    case SELECT_TIME = "Выбрать время ограничения для новых пользователей";
    case SET_TIME_TWO_HOURS = "Ограничивать новых пользователей на 2 часа";
    case SET_TIME_DAY = "Ограничивать новых пользователей на 24 часа";
    case SET_TIME_WEEK = "Ограничивать новых пользователей на неделю";
    case SET_TIME_MONTH = "Ограничивать новых пользователей на месяц";

    public function replyMessage()
    {
        return match ($this) {
            self::SETTINGS => 'Выберите ограничения для новых пользователей',
            self::ENABLE_SEND_MEDIA => 'Возможность отправки медиа-сообщений для новых участников включена',
            self::DISABLE_SEND_MEDIA => 'Возможность отправки медиа-сообщений для новых участников отключена',
            self::ENABLE_SEND_MESSAGES => 'Возможность отправки сообщений для новых участников включена',
            self::DISABLE_SEND_MESSAGES => 'Возможность отправки сообщений для новых участников отключена',
            self::DISABLE_ALL => 'Все ограничения для новых участников отключены',
            self::ENABLE_ALL => 'Все ограничения для новых участников включены',
            self::SELECT_TIME => 'Выберите время ограничения для новых пользователей',
            self::SET_TIME_TWO_HOURS => 'Установлено ограничение новых пользователей на 2 часа',
            self::SET_TIME_DAY => 'Установлено ограничение новых пользователей на 24 часа',
            self::SET_TIME_WEEK => 'Установлено ограничение новых пользователей на неделю',
            self::SET_TIME_MONTH => 'Установлено ограничение новых пользователей на месяц',
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