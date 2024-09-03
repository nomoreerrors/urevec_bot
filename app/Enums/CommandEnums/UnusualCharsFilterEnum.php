<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
use App\Enums\Traits\FiltersCases;
use App\Enums\Traits\GetValues;
use App\Enums\EnumHasRestrictionTimeInterface;

enum UnusualCharsFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use EnumValues;
    use FiltersCases;

    // case SETTINGS = "Настройки фильтра подозрительных символов";
    //Main cases
    case ENABLED_DISABLE = "Отключить фильтр подозрительных символов";
    case ENABLED_ENABLE = "Включить фильтр подозрительных символов";
    case EDIT_RESTRICTIONS = "Фильтр символов: Редактировать ограничения нарушителей";
    //Restrictions
    case RESTRICT_USER_DISABLE = "Фильтр символов: отключить все ограничения";
    case RESTRICT_USER_ENABLE = "Фильтр символов: включить все ограничения";
    case CAN_SEND_MESSAGES_DISABLE = "Фильтр символов: Запретить отправку сообщений нарушителям";
    case CAN_SEND_MESSAGES_ENABLE = "Фильтр символов: Разрешить отправку сообщений нарушителям";
    case CAN_SEND_MEDIA_DISABLE = "Фильтр символов: Запретить отправку медиа-сообщений нарушителям";
    case CAN_SEND_MEDIA_ENABLE = "Фильтр символов: Разрешить отправку медиа-сообщений нарушителям";
    case DELETE_MESSAGE_ENABLE = "Фильтр символов: Удалить сообщение нарушителя";
    case DELETE_MESSAGE_DISABLE = "Фильтр символов: Не удалять сообщение нарушителя";
    case DELETE_USER_ENABLE = "Фильтр символов: Удалять нарушителей";
    case DELETE_USER_DISABLE = "Фильтр символов: Не удалять нарушителей";
    // case RESTRICT_USERS_ENABLE = "Фильтр символов: Включить временные ограничения нарушителей";
    // case RESTRICT_USERS_DISABLE = "Фильтр символов: Отключить временные ограничения нарушителей ";

    /**
     *  Restriction time
     */
    case SELECT_RESTRICTION_TIME = "Фильтр подозрительных символов: Выбрать время ограничения для нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр символов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр символов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр символов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр символов: Ограничивать нарушителей на месяц";


    public static function getMainMenuCases(): array
    {
        return array_merge(
            self::getFiltersCases(),
            [
                //ADD ADDITIONAL CASES
            ]
        );
    }


    public static function getMainMenuCasesValues(): array
    {
        return array_merge(
            self::getFiltersCasesValues(),
            [
                //ADD ADDITIONAL CASES
            ]
        );
    }

    public function replyMessage(): string
    {
        return match ($this) {
                // self::SETTINGS => 'Фильтр подозрительных символов оберегает чат от спам-сообщений, которые пытаются обойти блокировки, заменяя обычное написание слов нестандартными символами',
            self::ENABLED_ENABLE => 'Фильтр подозрительных символов включен',
            self::ENABLED_DISABLE => 'Фильтр подозрительных символов отключен',
            self::SET_TIME_DAY => 'Фильтр символов: Ограничивать нарушителей на 24 часа',
            self::SET_TIME_MONTH => 'Фильтр символов: Ограничивать нарушителей на месяц',
            self::SET_TIME_WEEK => 'Фильтр символов: Ограничивать нарушителей на неделю',
            self::SET_TIME_TWO_HOURS => 'Фильтр символов: Ограничивать нарушителей на 2 часа',
            self::SELECT_RESTRICTION_TIME => 'Фильтр символов: Выбрать время ограничения для нарушителей',
            self::CAN_SEND_MESSAGES_DISABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MESSAGES_ENABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_DISABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_ENABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::DELETE_MESSAGE_ENABLE => 'Фильтр символов: Удалить сообщение нарушителя',
            self::DELETE_MESSAGE_DISABLE => 'Фильтр символов: Не удалять сообщение нарушителя',
            self::EDIT_RESTRICTIONS => 'Выберите ограничения для пользователей, попавших под фильтр',
            self::RESTRICT_USER_ENABLE => 'Все ограничения включены',
            self::RESTRICT_USER_DISABLE => 'Все ограничения отключены',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}

