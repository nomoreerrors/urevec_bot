<?php

namespace App\Services;


class CONSTANTS
{
    /**
     * TIME
     */
    public const MONTH = 2592000;
    public const WEEK = 604800;
    public const DAY = 86400;
    public const HOUR = 3600;
    public const MINUTE = 60;



    /**
     * ERRORS
     * @var string
     */
    public const EMPTY_PROPERTY = "ERROR: СВОЙСТВО КЛАССА НЕ УСТАНОВЛЕНО. " . PHP_EOL;
    public const BACK_TO_MAIN_MENU_FAILED = "ERROR: НЕ УДАЛОСЬ ВЫЙТИ ИЗ ГЛАВНОГО МЕНЮ. МАССИВ BACKMENU ПУСТ. " . PHP_EOL;
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
    public const GET_ADMINS_FAILED = "ERORR: НЕ УДАЛОСЬ ПОЛУЧИТЬ СПИСОК АДМИНИСТРАТОРОВ. " . PHP_EOL;
    public const SET_GROUP_CHAT_COMMANDS_FAILED = "ERROR: Failed to set group chat commands visibility " . PHP_EOL;
    public const SET_PRIVATE_CHAT_COMMANDS_FAILED = "ERROR: Failed to set private chat commands visibility " . PHP_EOL;
    public const EMPTY_ADMIN_IDS_ARRAY = "ERROR: EMPTY ADMIN IDS ARRAY " . PHP_EOL;
    public const USER_NOT_ALLOWED = "ERROR: USER NOT ALLOWED. ADMIN NOT SET OR NOT EXISTS IN DATABASE" . PHP_EOL;
    public const SELECTED_CHAT_NOT_SET = "ERROR: SELECTED CHAT NOT SET. " . PHP_EOL;
    public const SELECT_CHAT_FIRST = "ERROR: SELECT CHAT FIRST. " . PHP_EOL;
    public const REFRESH_BACK_MENU_FAILED = "ERROR: REFRESH BACK MENU FAILED. BACK MENU ARRAY IN CACHE IS EMPTY " . PHP_EOL;
    public const COMMAND_NOT_FOUND = "Команда не распознана";

    /**
     * SUCCSESS MESSAGES
     * @var string
     */
    public const NEW_MEMBER_RESTRICTED = "НОВЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const INVITED_USER_BLOCKED = "НОВЫЙ ПРИГЛАШЕННЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ " . PHP_EOL;
    public const MEMBER_BLOCKED = "ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН" . PHP_EOL;
    public const DEFAULT_RESPONSE = "ОБРАБОТКА ЗАВЕРШЕНА " . PHP_EOL;
    public const DELETED_BY_FILTER = "СООБЩЕНИЕ УДАЛЕНО ФИЛЬТРОМ FILTER SERVICE" . PHP_EOL;


    /**
     *  BOT COMMANDS
     */
    public const UNKNOWN_COMMAND = "Команда не распознана";
    public const MODERATION_SETTINGS_CMD = "/moderation_settings";
    public const START_CMD = "/start";
    public const BAN_SETTINGS_CMD = "/ban_settings";

    /**
     * RESTRICTIONS COMMANDS
     */
    public const RESTRICT_NEW_USERS_SETTINGS_CMD = "/restrict_new_users_settings";
    public const RESTRICT_NEW_USERS_FOR_24H_CMD = "/restrict_new_users_for_24_hours";
    public const RESTRICT_NEW_USERS_FOR_2H_CMD = "/restrict_new_users_for_2_hours";
    public const RESTRICT_NEW_USERS_FOR_1W_CMD = "/restrict_new_users_for_one_week";
    public const RESTRICT_NEW_USERS_FOR_MONTH_CMD = "/restrict_new_users_for_one_month";
    public const RESTRICT_MESSAGES_FOR_NEW_USERS_CMD = "/restrict_send_messages_for_new_users";
    public const RESTRICT_MEDIA_FOR_NEW_USERS_CMD = "/restrict_send_media_for_new_users";
    public const RESTRICT_STOP_RESTRICT_NEW_MEMBERS_CMD = "/restrict_stop_new_users_restriction"; // "Не ограничивать";
    public const RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD = "/restrict_set_new_users_restriction_time";

    /**
     * Restriction time
     */
    public const RESTIME_2H = 1;
    public const RESTIME_DAY = 2;
    public const RESTIME_WEEK = 3;
    public const RESTIME_MONTH = 4;
    public const RESTIME_NONE = 0;

    /**
     * FILTER COMMANDS
     */
    public const FILTER_SETTINGS_CMD = "/filter_settings";



    /** 
     * Reply messages
     */
    public const ADD_BOT_TO_GROUP = "Добавьте бота в свою группу и назначьте администратором,
             чтобы активировать работу." . PHP_EOL;




    /**
     * CACHE 
     */
    public const CACHE_CHAT_ADMINS_IDS = "chat_admins";
    public const CACHE_BAN_FORWARD_MESSAGES = "ban_forward_messages_";
    public const CACHE_ADMINS_GROUP_CHAT_COMMANDS_VISIBILITY = "bot_admins_group_chat_commands_status_";
    public const CACHE_MY_COMMANDS_SET = "my_commands_set_";
    public const CACHE_ADMINS_PRIVATE_CHATS_COMMANDS_VISIBILITY = "admins_private_chats_commands_visibility_";
    public const CACHE_ADMINS_IDS_NOT_SET = "СПИСОК АДМИНИСТРАТОРОВ НЕ УСТАНОВЛЕН В КЭШЕ. " . PHP_EOL;
    public const CACHE_LAST_COMMAND = "last_command_";


    /**
     *  Reply messages
     */
    public const REPLY_RESTRICT_SELECT_RESTRICTIONS_FOR_NEW_USERS = "Выберите ограничения для новых пользователей. По умолчанию ограничения включены" . PHP_EOL;
    public const REPLY_RESTRICT_SELECT_RESTRICTION_TIME_FOR_NEW_USERS = "Выберите время ограничения для новых пользователей. По умолчанию 24 часа." . PHP_EOL;
    public const REPLY_RESTRICT_NEW_USERS_FOR_2H = "Установлено ограничение новых пользователей на 2 часа";
    public const REPLY_RESTRICT_NEW_USERS_FOR_24H = 'Установлено ограничение новых пользователей на 24 часа';
    public const REPLY_RESTRICT_NEW_USERS_FOR_1W = "Установлено ограничение новых пользователей на 1 неделю";
    public const REPLY_RESTRICT_NEW_USERS_FOR_MONTH = "Установлено ограничение новых пользователей на 1 месяц";

}

