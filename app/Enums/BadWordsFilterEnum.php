<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum BadWordsFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use Exists;
    /** * Bad words */
    case SETTINGS = "Фильтр запрещенных слов";
    case DISABLE = "Отключить фильтр слов";
    case ENABLE = "Включить фильтр слов";
    case ADD_WORDS = "Добавить запрещенные слова";
    case DELETE_WORDS = "Удалить запрещенные слова";
    case GET_WORDS = "Посмотреть мой список запрещенных слов";
    case SEND_MESSAGES_DISABLE = "Фильтр слов: Запретить отправку сообщений нарушителям";
    case SEND_MESSAGES_ENABLE = "Фильтр слов: Разрешить отправку сообщений нарушителям";
    case SEND_MEDIA_DISABLE = "Фильтр слов: Запретить отправку медиа-сообщений нарушителям";
    case SEND_MEDIA_ENABLE = "Фильтр слов: Разрешить отправку медиа-сообщений нарушителям";
    case EDIT_RESTRICTIONS = "Фильтр слов: Редактировать ограничения нарушителей";
    case RESTRICTIONS_DISABLE_ALL = "Фильтр слов: отключить все ограничения";
    case RESTRICTIONS_ENABLE_ALL = "Фильтр слов: включить все ограничения";
    case DELETE_MESSAGES_ENABLE = "Фильтр слов: Удалить сообщение нарушителя";
    case DELETE_MESSAGES_DISABLE = "Фильтр слов: Не удалять сообщение нарушителя";
    case RESTRICT_USERS_ENABLE = "Фильтр слов: Включить временные ограничения нарушителей";
    case RESTRICT_USERS_DISABLE = "Фильтр слов: Отключить временные ограничения нарушителей ";

    /**
     * Restriction time
     */
    case SELECT_RESTRICTION_TIME = "Фильтр слов: Выбрать время ограничения нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр  слов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр  слов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр  слов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр  слов: Ограничивать нарушителей на месяц";


    public function replyMessage(): string
    {
        return match ($this) {
            self::SETTINGS => 'Установите настройки фильтра запрещенных слов',
            self::ADD_WORDS => 'Установите список в формате: BAD WORDS:слово1,слово2,слово3',
            self::DISABLE => 'Фильтр отключен',
            self::ENABLE => 'Фильтр включен',
            self::DELETE_MESSAGES_DISABLE => 'Удаление сообщений отключено',
            self::DELETE_MESSAGES_ENABLE => 'Удаление сообщений включено',
            self::RESTRICT_USERS_DISABLE => 'Все ограничения отключены',
            self::RESTRICT_USERS_ENABLE => 'Все ограничения включены',
            self::SELECT_RESTRICTION_TIME => 'Выберите время ограничения нарушителей',
            self::SET_TIME_MONTH => 'Установлено ограничение нарушителей на месяц',
            self::SET_TIME_WEEK => 'Установлено ограничение нарушителей на неделю',
            self::SET_TIME_DAY => 'Установлено ограничение нарушителей на 24 часа',
            self::SET_TIME_TWO_HOURS => 'Установлено ограничение нарушителей на 2 часа',
            self::EDIT_RESTRICTIONS => 'Выберите ограничения для пользователей, попавших под фильтр',
            self::RESTRICTIONS_ENABLE_ALL => 'Все ограничения включены',
            self::RESTRICTIONS_DISABLE_ALL => 'Все ограничения отключены',
            self::SEND_MESSAGES_DISABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::SEND_MESSAGES_ENABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::SEND_MEDIA_DISABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::SEND_MEDIA_ENABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}
