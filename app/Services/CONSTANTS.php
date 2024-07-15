<?php

namespace App\Services;


class CONSTANTS
{
    public function __construct()
    {
        // dd(get_called_class());
    }
    /**
     * ERRORS
     * @var string
     */
    public const EMPTY_PROPERTY = "ERROR: СВОЙСТВО КЛАССА НЕ УСТАНОВЛЕНО. " . PHP_EOL;
    public const REQUEST_IP_NOT_ALLOWED = "ERROR: ЗАПРОС К СЕРВЕРУ С НЕИЗВЕСТНОГО IP ИЛИ СПИСОК РАЗРЕШЕННЫХ АДРЕСОВ НЕ УСТАНОВЛЕН. " . PHP_EOL;
    public const EMPTY_ARRAY_KEY = "ERROR: КЛЮЧ МАССИВА НЕ СУЩЕСТВУЕТ ИЛИ ИСПОЛЬЗУЕМОЕ СВОЙСТВО МОДЕЛИ НЕ УСТАНОВЛЕНО. " . PHP_EOL;
    public const UNKNOWN_OBJECT_TYPE = "ERROR: НЕИЗВЕСТНЫЙ ТИП ВХОДЯЩЕГО СООБЩЕНИЯ ИЛИ СВОЙСТВО MESSAGE_TYPE МОДЕЛИ НЕ УСТАНОВЛЕНО. " . PHP_EOL;
    public const EMPTY_ENVIRONMENT_VARIABLES = "ERROR: ПЕРЕМЕННАЯ ОКРУЖЕНИЯ НЕ УСТАНОВЛЕНА ИЛИ НЕДОСТУПЕН ФАЙЛ .ENV " . PHP_EOL;
    public const REQUEST_CHAT_ID_NOT_ALLOWED = "ERROR: ВХОДЯЩИЙ ЗАПРОС С НЕИЗВЕСТНОГО CHAT_ID ИЛИ СПИСОК РАЗРЕШЕННЫХ ЧАТОВ НЕ УСТАНОВЛЕН." . PHP_EOL;
    public const RESTRICT_NEW_USER_FAILED = "ERROR: НЕ УДАЛОСЬ ОГРАНИЧИТЬ ПРАВА НОВОГО ПОЛЬЗОВАТЕЛЯ. " . PHP_EOL;
    public const BAN_USER_FAILED = "ERROR: НЕ УДАЛОСЬ ЗАБЛОКИРОВАТЬ ПОЛЬЗОВАТЕЛЯ. " . PHP_EOL;

    /**
     * SUCCSESS MESSAGES
     * @var string
     */
    public const NEW_MEMBER_RESTRICTED = "НОВЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const INVITED_USER_BLOCKED = "НОВЫЙ ПРИГЛАШЕННЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const MEMBER_BLOCKED = "ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const DEFAULT_RESPONSE = "ОБРАБОТКА ЗАВЕРШЕНА " . PHP_EOL;
    public const DELETED_BY_FILTER = "СООБЩЕНИЕ УДАЛЕНО ФИЛЬТРОМ FILTER SERVICE" . PHP_EOL;
}
