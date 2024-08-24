<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum UnusualCharsFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use Exists;

    /**
     * Unusual chars
     */
    case SETTINGS = "Настройки фильтра подозрительных символов";
    case DISABLE = "Отключить фильтр подозрительных символов";
    case ENABLE = "Включить фильтр подозрительных символов";
    case EDIT_RESTRICTIONS = "Фильтр символов: Редактировать ограничения нарушителей";
    case RESTRICTIONS_DISABLE = "Фильтр символов: отключить все ограничения";
    case RESTRICTIONS_ENABLE = "Фильтр символов: включить все ограничения";
    case SEND_MESSAGES_DISABLE = "Фильтр символов: Запретить отправку сообщений нарушителям";
    case SEND_MESSAGES_ENABLE = "Фильтр символов: Разрешить отправку сообщений нарушителям";
    case SEND_MEDIA_DISABLE = "Фильтр символов: Запретить отправку медиа-сообщений нарушителям";
    case SEND_MEDIA_ENABLE = "Фильтр символов: Разрешить отправку медиа-сообщений нарушителям";
    case DELETE_MESSAGES_ENABLE = "Фильтр символов: Удалить сообщение нарушителя";
    case DELETE_MESSAGES_DISABLE = "Фильтр символов: Не удалять сообщение нарушителя";
    case RESTRICT_USERS_ENABLE = "Фильтр символов: Включить временные ограничения нарушителей";
    case RESTRICT_USERS_DISABLE = "Фильтр символов: Отключить временные ограничения нарушителей ";

    /**
     *  Restriction time
     */
    case SELECT_RESTRICTION_TIME = "Фильтр подозрительных символов: Выбрать время ограничения для нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр символов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр символов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр символов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр символов: Ограничивать нарушителей на месяц";



    public function replyMessage(): string
    {
        return match ($this) {
            self::SETTINGS => 'Фильтр подозрительных символов оберегает чат от спам-сообщений, которые пытаются обойти блокировки, заменяя обычное написание слов нестандартными символами',
            self::ENABLE => 'Фильтр подозрительных символов включен',
            self::DISABLE => 'Фильтр подозрительных символов отключен',
            self::SET_TIME_DAY => 'Фильтр символов: Ограничивать нарушителей на 24 часа',
            self::SET_TIME_MONTH => 'Фильтр символов: Ограничивать нарушителей на месяц',
            self::SET_TIME_WEEK => 'Фильтр символов: Ограничивать нарушителей на неделю',
            self::SET_TIME_TWO_HOURS => 'Фильтр символов: Ограничивать нарушителей на 2 часа',
            self::SELECT_RESTRICTION_TIME => 'Фильтр символов: Выбрать время ограничения для нарушителей',
            self::SEND_MESSAGES_DISABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::SEND_MESSAGES_ENABLE => 'Фильтр символов: Отправка сообщений нарушителям запрещена',
            self::SEND_MEDIA_DISABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::SEND_MEDIA_ENABLE => 'Фильтр символов: Отправка медиа-сообщений нарушителям запрещена',
            self::DELETE_MESSAGES_ENABLE => 'Фильтр символов: Удалить сообщение нарушителя',
            self::DELETE_MESSAGES_DISABLE => 'Фильтр символов: Не удалять сообщение нарушителя',
            self::RESTRICT_USERS_ENABLE => 'Фильтр символов: Включить временные ограничения нарушителей',
            self::RESTRICT_USERS_DISABLE => 'Фильтр символов: Отключить временные ограничения нарушителей',
            self::EDIT_RESTRICTIONS => 'Выберите ограничения для пользователей, попавших под фильтр',
            self::RESTRICTIONS_ENABLE => 'Все ограничения включены',
            self::RESTRICTIONS_DISABLE => 'Все ограничения отключены',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}

