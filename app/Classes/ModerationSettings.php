<?php

namespace App\Classes;

use App\Services\CONSTANTS;

class ModerationSettings
{
    /**
     * Send moderation settings buttons to user
     * @return void
     */
    public function send(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::NEW_USERS_RESTRICT_SETTINGS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::BAN_SETTINGS_CMD)
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
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD)
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD)
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD)
            ->addRow()
            ->addButton(CONSTANTS::STOP_RESTRICT_NEW_MEMBERS_CMD)
            ->get();
        app("botService")->sendMessage("Установите срок ограничения для новых пользователей", $keyBoard);
    }

}