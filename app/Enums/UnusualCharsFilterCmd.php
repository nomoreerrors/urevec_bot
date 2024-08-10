<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum UnusualCharsFilterCmd: string implements EnumHasRestrictionTimeInterface
{
    use Exists;

    /**
     * Unusual chars
     */
    case SETTINGS = "Настройки фильтра иероглифов и подозрительных символов";
    case DISABLE_UNUSUAL_CHARS = "Отключить фильтр иероглифов и подозрительных символов";
    case ENABLE_UNUSUAL_CHARS = "Включить фильтр иероглифов и подозрительных символов";
    case SELECT_TIME = "Фильтр подозрительных символов: Выбрать время ограничения для нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр подозрительных символов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр подозрительных символов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр подозрительных символов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр подозрительных символов: Ограничивать нарушителей на месяц";



    public function replyMessage(): string
    {
        return match ($this) {
            self::SETTINGS => 'Фильтр иероглифов и подозрительных символов оберегает чат от спам-сообщений, которые пытаются обойти блокировки, заменяя обычное написание слов нестандартными символами',
            self::ENABLE_UNUSUAL_CHARS => 'Фильтр иероглифов и подозрительных символов включен',
            self::DISABLE_UNUSUAL_CHARS => 'Фильтр иероглифов и подозрительных символов отключен',
            self::SET_TIME_DAY => 'Фильтр подозрительных символов: Ограничивать нарушителей на 24 часа',
            self::SET_TIME_MONTH => 'Фильтр подозрительных символов: Ограничивать нарушителей на месяц',
            self::SET_TIME_WEEK => 'Фильтр подозрительных символов: Ограничивать нарушителей на неделю',
            self::SET_TIME_TWO_HOURS => 'Фильтр подозрительных символов: Ограничивать нарушителей на 2 часа',
            self::SELECT_TIME => 'Фильтр подозрительных символов: Выбрать время ограничения для нарушителей',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}
