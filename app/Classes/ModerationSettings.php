<?php

namespace App\Classes;

use App\Services\CONSTANTS;

class ModerationSettings implements ReplyInterface
{
    /**
     * Send moderation settings buttons to user
     * @return void
     */
    public function send(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD)
            // ->addRow()
            // ->addButton(CONSTANTS::BAN_SETTINGS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::FILTER_SETTINGS_CMD)
            ->get();

        app("botService")->sendMessage("Модерация чата", $keyBoard);
    }

    /**
     * Send buttons with new joined users restrictions settings to user 
     * @return void
     */
    public function sendNewUsersRestrictionsSettings(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_MESSAGES_FOR_NEW_USERS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_MEDIA_FOR_NEW_USERS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD)
            ->get();
        app("botService")->sendMessage("Настройки ограничения для новых пользователей", $keyBoard);
    }

    public function sendNewUsersRestrictionsTimeSettings(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD)
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD)
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_STOP_RESTRICT_NEW_MEMBERS_CMD)
            ->get();
        app("botService")->sendMessage("Установите срок ограничения для новых пользователей", $keyBoard);
    }

    public function sendFilterSettingsButtons()
    {

    }
}