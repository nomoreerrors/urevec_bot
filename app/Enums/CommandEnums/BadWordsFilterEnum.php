<?php

namespace App\Enums\CommandEnums;

use App\Enums\Traits\EnumValues;
// use App\Enums\Traits\GetValues;
use App\Enums\Traits\FiltersCases;
use App\Enums\EnumHasRestrictionTimeInterface;

enum BadWordsFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use EnumValues;
    use FiltersCases;

    //Specific cases
    case ADD_WORDS = "Добавить запрещенные слова";
    case DELETE_WORDS = "Удалить запрещенные слова";
    case GET_WORDS = "Посмотреть мой список запрещенных слов";
    case DELETE_MESSAGE_ENABLE = "Фильтр слов: включить удаление сообщений";
    case DELETE_MESSAGE_DISABLE = "Фильтр слов: отключить удаление сообщений";
    case EDIT_RESTRICTIONS = "Фильтр слов: Редактировать ограничения нарушителей";

    case ENABLED_DISABLE = "Отключить фильтр слов";
    case ENABLED_ENABLE = "Включить фильтр слов";
    //Restricitons
    case CAN_SEND_MESSAGES_DISABLE = "Фильтр слов: Запретить отправку сообщений нарушителям";
    case CAN_SEND_MESSAGES_ENABLE = "Фильтр слов: Разрешить отправку сообщений нарушителям";
    case CAN_SEND_MEDIA_DISABLE = "Фильтр слов: Запретить отправку медиа-сообщений нарушителям";
    case CAN_SEND_MEDIA_ENABLE = "Фильтр слов: Разрешить отправку медиа-сообщений нарушителям";
    case RESTRICT_USER_DISABLE = "Фильтр слов: отключить все ограничения";
    case RESTRICT_USER_ENABLE = "Фильтр слов: включить все ограничения";
    case DELETE_USER_DISABLE = "Фильтр слов: Не удалять пользователя";
    case DELETE_USER_ENABLE = "Фильтр слов: Удалять пользователя";


    // Restriction time
    case SELECT_RESTRICTION_TIME = "Фильтр слов: Выбрать время ограничения нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр  слов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр  слов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр  слов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр  слов: Ограничивать нарушителей на месяц";


    public static function getMainMenuCases(): array
    {
        return array_merge(
            self::getFiltersCases(),
            [
                self::ADD_WORDS,
                self::GET_WORDS,
                self::DELETE_WORDS,
            ]
        );
    }

    public static function getMainMenuCasesValues(): array
    {
        return array_merge(
            self::getFiltersCasesValues(),
            [
                self::ADD_WORDS->value,
                self::GET_WORDS->value,
                self::DELETE_WORDS->value,
            ]
        );
    }


    public function replyMessage(): string
    {
        return match ($this) {
            self::ADD_WORDS => 'Установите список в формате: BAD WORDS:слово1,слово2,слово3',
            self::ENABLED_DISABLE => 'Фильтр слов отключен',
            self::ENABLED_ENABLE => 'Фильтр слов включен',
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
            self::CAN_SEND_MESSAGES_DISABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MESSAGES_ENABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_DISABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::CAN_SEND_MEDIA_ENABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}

