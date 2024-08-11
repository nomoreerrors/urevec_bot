<?php

namespace App\Enums;

use App\Enums\Traits\Exists;

enum BadWordsFilterCmd: string implements EnumHasRestrictionTimeInterface
{
    use Exists;

    /**
     * Bad words
     */
    case SETTINGS = "Фильтр запрещенных слов";
    case BAD_WORDS_DISABLE = "Отключить фильтр слов";
    case BAD_WORDS_ENABLE = "Включить фильтр слов";
    case BAD_WORDS_ADD_NEW = "Добавить запрещенные слова";
    case BAD_WORDS_DELETE = "Удалить запрещенные слова";
    case BAD_WORDS_GET = "Посмотреть мой список запрещенных слов";
    case BAD_WORDS_DISABLE_MESSAGES = "Фильтр слов: Запретить отправку сообщений нарушителям";
    case BAD_WORDS_ENABLE_MESSAGES = "Фильтр слов: Разрешить отправку сообщений нарушителям";
    case BAD_WORDS_DELETE_MESSAGES_ENABLE = "Фильтр слов: Удалить сообщение нарушителя";
    case BAD_WORDS_DELETE_MESSAGES_DISABLE = "Фильтр слов: Не удалять сообщение нарушителя";
    case BAD_WORDS_RESTRICT_USER_ENABLE = "Фильтр слов: Включить временные ограничения нарушителей";
    case BAD_WORDS_RESTRICT_USER_DISABLE = "Фильтр слов: Отключить временные ограничения нарушителей ";

    /**
     * Restriction time
     */
    case SELECT_TIME = "Фильтр слов: Выбрать время ограничения нарушителей";
    case SET_TIME_TWO_HOURS = "Фильтр  слов: Ограничивать нарушителей на 2 часа";
    case SET_TIME_DAY = "Фильтр  слов: Ограничивать нарушителей на 24 часа";
    case SET_TIME_WEEK = "Фильтр  слов: Ограничивать нарушителей на неделю";
    case SET_TIME_MONTH = "Фильтр  слов: Ограничивать нарушителей на месяц";

    // /**
    //  * Unusual chars
    //  */
    // case UNUSUAL_CHARS = "Фильтр иероглифов и подозрительных символов";
    // case DISABLE_UNUSUAL_CHARS = "Отключить фильтр иероглифов и подозрительных символов";
    // case ENABLE_UNUSUAL_CHARS = "Включить фильтр иероглифов и подозрительных символов";


    public function replyMessage(): string
    {
        return match ($this) {
            self::SETTINGS => 'Установите настройки фильтра запрещенных слов',
            self::BAD_WORDS_ADD_NEW => 'Установите список в формате: BAD WORDS:слово1,слово2,слово3',
                // self::UNUSUAL_CHARS => 'Фильтр иероглифов и подозрительных символов оберегает чат от спам-сообщений, которые пытаются обойти блокировки, заменяя обычное написание слов нестандартными символами',
                // self::ENABLE_UNUSUAL_CHARS => 'Фильтр иероглифов и подозрительных символов включен',
                // self::DISABLE_UNUSUAL_CHARS => 'Фильтр иероглифов и подозрительных символов отключен',
            self::BAD_WORDS_DISABLE => 'Фильтр отключен',
            self::BAD_WORDS_ENABLE => 'Фильтр включен',
            self::BAD_WORDS_DELETE_MESSAGES_DISABLE => 'Удаление сообщений отключено',
            self::BAD_WORDS_DELETE_MESSAGES_ENABLE => 'Удаление сообщений включено',
            self::BAD_WORDS_RESTRICT_USER_DISABLE => 'Все ограничения отключены',
            self::BAD_WORDS_RESTRICT_USER_ENABLE => 'Все ограничения включены',
            self::SELECT_TIME => 'Выберите время ограничения нарушителей',
            self::SET_TIME_MONTH => 'Установлено ограничение нарушителей на месяц',
            self::SET_TIME_WEEK => 'Установлено ограничение нарушителей на неделю',
            self::SET_TIME_DAY => 'Установлено ограничение нарушителей на 24 часа',
            self::SET_TIME_TWO_HOURS => 'Установлено ограничение нарушителей на 2 часа',
        };
    }

    public function withChatTitle(string $chatTitle)
    {
        return $this->replyMessage() . " " . "для чата: " . $chatTitle;
    }

}
