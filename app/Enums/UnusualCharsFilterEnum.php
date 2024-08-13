<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum UnusualCharsFilterEnum: string implements EnumHasRestrictionTimeInterface
{
    use Exists;

    /**
     * Unusual chars
     */
    case SETTINGS = "Настройки фильтра символов и подозрительных символов";
    case DISABLE = "Отключить фильтр подозрительных символов";
    case ENABLE = "Включить фильтр подозрительных символов";
    case DISABLE_MESSAGES = "Фильтр символов: Запретить отправку сообщений нарушителям";
    case ENABLE_MESSAGES = "Фильтр символов: Разрешить отправку сообщений нарушителям";
    case DELETE_MESSAGES_ENABLE = "Фильтр символов: Удалить сообщение нарушителя";
    case DELETE_MESSAGES_DISABLE = "Фильтр символов: Не удалять сообщение нарушителя";
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
            self::DISABLE_MESSAGES => 'Фильтр символов: Запретить отправку сообщений нарушителям',
            self::ENABLE_MESSAGES => 'Фильтр символов: Разрешить отправку сообщений нарушителям',
            self::DELETE_MESSAGES_ENABLE => 'Фильтр символов: Удалить сообщение нарушителя',
            self::DELETE_MESSAGES_DISABLE => 'Фильтр символов: Не удалять сообщение нарушителя',
            self::RESTRICT_USERS_ENABLE => 'Фильтр символов: Включить временные ограничения нарушителей',
            self::RESTRICT_USERS_DISABLE => 'Фильтр символов: Отключить временные ограничения нарушителей',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}

