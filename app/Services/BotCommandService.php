<?php

namespace App\Services;

use App\Classes\ReplyKeyboardMarkup;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Enums\COMMAND;

class BotCommandService
{
    /**
     * Class constructor.
     */
    public function __construct(
        private string $command,
        private int $fromId
    ) {
        $this->determineBotCommand();
    }


    public function determineBotCommand()
    {
        switch ($this->command) {
            case CONSTANTS::MODERATION_SETTINGS_CMD:
                $this->moderationSettingsHandler();
                break;
            case CONSTANTS::NEW_USERS_RESTRICT_SETTINGS_CMD:
                $this->newUsersRestrictSettingsHandler();
                break;
            case CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD:
                $this->cacheRestriction(CONSTANTS::HOUR * 2);
                break;
            case CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD:
                $this->cacheRestriction(CONSTANTS::DAY);
                break;
            case CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD:
                $this->cacheRestriction(CONSTANTS::WEEK);
                break;
            case CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD:
                $this->cacheRestriction(CONSTANTS::MONTH);
                break;
            case CONSTANTS::STOP_RESTRICT_NEW_MEMBERS_CMD:
                $this->cacheRestriction(0);
                break;
            case CONSTANTS::FILTER_SETTINGS_CMD:
                // Handle filter settings command
                break;
            case CONSTANTS::BAN_SETTINGS_CMD:
                // Handle ban settings command
                break;
            default:
                app("botService")->sendMessage("Неизвестная команда");
                return response(CONSTANTS::UNKNOWN_CMD, Response::HTTP_OK);
        }
    }

    private function moderationSettingsHandler(): void
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

    private function newUsersRestrictSettingsHandler(): void
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

    private function cacheRestriction(int $duration): void
    {
        Cache::put("new_users_restriction_time", $duration);
    }


}
