<?php

namespace App\Classes;

use App\Services\CONSTANTS;
use App\Models\Chat;

class RestrictUserCommand implements ReplyInterface
{
    public function __construct(private string $command, private Chat $chat)
    {
        $this->handle();        //
    }

    private function handle()
    {
        switch ($this->command) {
            case CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD:
                $this->send();
                break;
            case CONSTANTS::RESTRICT_MEDIA_FOR_NEW_USERS_CMD:
                $this->setNewUsersRestrictions(false, true);
                break;
            case CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD:
                $this->setNewUsersRestrictionTime();
                break;
            default:
                break;
        }
    }

    public function send(): void
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_MESSAGES_FOR_NEW_USERS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_MEDIA_FOR_NEW_USERS_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD)
            ->get();

        app("botService")->sendMessage(
            CONSTANTS::REPLY_RESTRICT_SELECT_RESTRICTIONS_FOR_NEW_USERS,
            $keyBoard
        );
    }

    protected function setNewUsersRestrictionTime()
    {
        $this->chat->newUsersRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => $this->getRestrictionTime()
        ]);
        return $this;
    }

    protected function setNewUsersRestrictions(bool $canSendMessages = false, $canSendMedia = false)
    {
        $restrict = $canSendMessages || $canSendMedia ? 1 : 0;

        $this->chat->newUsersRestrictions()->update([
            'restrict_new_users' => $restrict,
            'can_send_messages' => $canSendMessages,
            'can_send_media' => $canSendMedia
        ]);
        return $this;
    }

    protected function getRestrictionTime(): int
    {
        return match ($this->command) {
            CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD => CONSTANTS::RESTIME_2H,
            CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD => CONSTANTS::RESTIME_DAY,
            CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD => CONSTANTS::RESTIME_WEEK,
            CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD => CONSTANTS::RESTIME_MONTH,
            CONSTANTS::RESTRICT_STOP_RESTRICT_NEW_MEMBERS_CMD => CONSTANTS::RESTIME_NONE,
            default => null,
        };
    }
}
