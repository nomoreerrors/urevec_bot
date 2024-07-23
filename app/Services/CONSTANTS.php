<?php

namespace App\Services;


class CONSTANTS
{
    public function __construct()
    {
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
    public const UNKNOWN_CMD = "ERROR: КОМАНДА НЕ РАСПОЗНАНА " . PHP_EOL;
    public const SET_MY_COMMANDS_FAILED = "ERROR: НЕ УДАЛОСЬ УСТАНОВИТЬ КОМАНДЫ. " . PHP_EOL;
    public const SEND_MESSAGE_FAILED = "ERROR: НЕ УДАЛОСЬ ОТПРАВИТЬ СООБЩЕНИЕ. " . PHP_EOL;
    public const DELETE_MESSAGE_FAILED = "ERROR: НЕ УДАЛОСЬ УДАЛИТЬ СООБЩЕНИЕ. " . PHP_EOL;
    public const WRONG_INSTANCE_TYPE = "ERROR:̆ НЕВЕРНЫЙ ТИП ОБЪЕКТА. " . PHP_EOL;
    public const RESTRICT_MEMBER_FAILED = "ERROR: НЕ УДАЛОСЬ ОГРАНИЧИТЬ ПРАВА ПОЛЬЗОВАТЕЛЯ. " . PHP_EOL;

    /**
     * SUCCSESS MESSAGES
     * @var string
     */
    public const NEW_MEMBER_RESTRICTED = "НОВЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const INVITED_USER_BLOCKED = "НОВЫЙ ПРИГЛАШЕННЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const MEMBER_BLOCKED = "ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const DEFAULT_RESPONSE = "ОБРАБОТКА ЗАВЕРШЕНА " . PHP_EOL;
    public const DELETED_BY_FILTER = "СООБЩЕНИЕ УДАЛЕНО ФИЛЬТРОМ FILTER SERVICE" . PHP_EOL;


    /**
     *  BOT COMMANDS
     */
    public const UNKNOWN_COMMAND = "Команда не распознана";
    public const MODERATION_SETTINGS_CMD = "/moderation_settings";
    public const NEW_USERS_RESTRICT_SETTINGS_CMD = "/new_users_restrict_settings";
    public const FILTER_SETTINGS_CMD = "/filter_settings";
    public const BAN_SETTINGS_CMD = "/ban_settings";
    public const RESTRICT_NEW_USERS_FOR_24H_CMD = "/24 часа";
    public const RESTRICT_NEW_USERS_FOR_2H_CMD = "/2 часа";
    public const RESTRICT_NEW_USERS_FOR_1W_CMD = "/Неделя";
    public const RESTRICT_NEW_USERS_FOR_MONTH_CMD = "/Месяц";
    public const STOP_RESTRICT_NEW_MEMBERS_CMD = "/Не ограничивать";

    /**
     * TIME 
     */
    public const DAY = 86400;
    public const HOUR = 3600;
    public const WEEK = 604800;
    public const MONTH = 2592000;


    /**
     * CACHE 
     */
    public const CACHE_CHAT_ADMINS_IDS = "chat_admins";
    public const CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY = "bot_admins_group_chat_commands_status_";
    public const CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY = "admins_private_chats_commands_visibility_";
    public const CACHE_ADMINS_IDS_NOT_SET = "СПИСОК АДМИНИСТРАТОРОВ НЕ УСТАНОВЛЕН В КЭШЕ. " . PHP_EOL;
}
