<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
// use App\Enums\Traits\GetValues;
use App\Enums\Traits\FiltersCases;
use App\Enums\EnumHasRestrictionTimeInterface;

enum LinksFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use EnumValues;
    use FiltersCases;

    //Specific cases
    case DELETE_MESSAGE_ENABLE = "Фильтр ссылок: включить удаление сообщений";
    case DELETE_MESSAGE_DISABLE = "Фильтр ссылок: отключить удаление сообщений";
    case EDIT_RESTRICTIONS = "Фильтр ссылок: Редактировать ограничения нарушителей";

    case ENABLED_DISABLE = "Отключить фильтр ссылок";
    case ENABLED_ENABLE = "Включить фильтр ссылок";
    //Restricitons
    case CAN_SEND_MESSAGES_DISABLE = "Фильтр ссылок: Запретить отправку сообщений нарушителям";
    case CAN_SEND_MESSAGES_ENABLE = "Фильтр ссылок: Разрешить отправку сообщений нарушителям";
    case CAN_SEND_MEDIA_DISABLE = "Фильтр ссылок: Запретить отправку медиа-сообщений нарушителям";
    case CAN_SEND_MEDIA_ENABLE = "Фильтр ссылок: Разрешить отправку медиа-сообщений нарушителям";
    case RESTRICT_USER_DISABLE = "Фильтр ссылок: отключить все ограничения";
    case RESTRICT_USER_ENABLE = "Фильтр ссылок: включить все ограничения";
    case DELETE_USER_DISABLE = "Фильтр ссылок: Не удалять пользователя";
    case DELETE_USER_ENABLE = "Фильтр ссылок: Удалять пользователя";


    // Restriction time
    case SELECT_RESTRICTION_TIME = "Фильтр ссылок: Выбрать время ограничения нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр  ссылок: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр  ссылок: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр  ссылок: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр  ссылок: Ограничивать нарушителей на месяц";


    public static function getMainMenuCases(): array
    {
        return self::getFiltersCases();
    }

    public static function getMainMenuCasesValues(): array
    {
        return array_merge(
            self::getFiltersCasesValues(),
        );
    }


    public function replyMessage(): string
    {
        return match ($this) {
            self::ENABLED_DISABLE => 'Фильтр ссылок отключен',
            self::ENABLED_ENABLE => 'Фильтр ссылок включен',
            self::DELETE_MESSAGE_DISABLE => 'Удаление сообщений отключено',
            self::DELETE_MESSAGE_ENABLE => 'Удаление сообщений включено',
            self::SELECT_RESTRICTION_TIME => 'Выберите время ограничения нарушителей',
            self::SET_TIME_MONTH => 'Установлено ограничение нарушителей на месяц',
            self::SET_TIME_WEEK => 'Установлено ограничение нарушителей на неделю',
            self::SET_TIME_DAY => 'Установлено ограничение нарушителей на 24 часа',
            self::SET_TIME_TWO_HOURS => 'Установлено ограничение нарушителей на 2 часа',
            self::EDIT_RESTRICTIONS => 'Выберите ограничения для пользователей, попавших под фильтр',
            self::RESTRICT_USER_ENABLE => 'Все ограничения включены',
            self::RESTRICT_USER_DISABLE => 'Все ограничения отключены',
            self::CAN_SEND_MESSAGES_DISABLE => 'Фильтр ссылок: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MESSAGES_ENABLE => 'Фильтр ссылок: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_DISABLE => 'Фильтр ссылок: Отправка медиа-сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_ENABLE => 'Фильтр ссылок: Отправка медиа-сообщений нарушителям запрещена',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}

