<?php

namespace App\Classes;

use App\Enums\ResNewUsersCmd;
use App\Services\CONSTANTS;
use App\Models\Chat;
use App\Services\TelegramBotService;

class RestrictUserCommand implements ReplyInterface
{
    private TelegramBotService $botService;
    private Chat $chat;

    public function __construct(private string $command)
    {
        $this->botService = app("botService");
        $this->chat = $this->botService->getChat();
        $this->handle();        //
    }

    private function handle()
    {
        switch ($this->command) {
            case ResNewUsersCmd::SETTINGS->value:
                $this->send();
                break;
            case ResNewUsersCmd::ENABLE_SEND_MEDIA->value:
                $this->toggleSendMedia();
                break;
            // ResNewUsersCmd::DISABLE_SEND_MEDIA->value => fn() => $this->toggleSendMedia(),
            // ResNewUsersCmd::ENABLE_SEND_MESSAGES->value,
            // ResNewUsersCmd::DISABLE_SEND_MESSAGES->value => fn() => $this->toggleSendMessages(),
            // ResNewUsersCmd::ENABLE_ALL_RESTRICTIONS->value,
            // ResNewUsersCmd::DISABLE_ALL_RESTRICTIONS->value => fn() => $this->toggleAllRestrictions()
        }
        ;

        $stop = 0;
        //         case SETTINGS = "/new_users_restrictions_settings";
        // case ENABLE_SEND_MEDIA = "/new_users_restrictions_send_media_enabled";
        // case DISABLE_SEND_MEDIA = "/new_users_restrictions_send_media_disabled";
        // case ENABLE_SEND_MESSAGES = "/new_users_restrictions_send_messages_enabled";
        // case DISABLE_SEND_MESSAGES = "/new_users_restrictions_send_messages_disabled";
        // case STOP_ALL_RESTRICTIONS = "/new_users_restrictions_stop_all";
        // switch ($this->command) {
        //     case CONSTANTS::RESTRICT_NEW_USERS_SETTINGS_CMD:
        //         $this->send();
        //         break;
        //     case CONSTANTS::RESTRICT_SET_NEW_USERS_RESTRICTION_TIME_CMD:
        //         $this->sendRestrictionTimeButtons();
        //         break;
        //     case CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD ||
        //     CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD ||
        //     CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD ||
        //     CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD:
        //         $this->setNewUsersRestrictionTime();
        //     case CONSTANTS::RESTRICT_MESSAGES_FOR_NEW_USERS_CMD:
        //         $this->toggleCanSendMessages();
        //     case CONSTANTS::RESTRICT_MEDIA_FOR_NEW_USERS_CMD:
        //         $this->stopAllNewMembersRestrictions();
        //     default:
        //         break;
        // }
    }

    public function send(): void
    {
        $canSendMedia = $this->chat->newUserRestrictions->can_send_media === 1;
        $canSendMessages = $this->chat->newUserRestrictions->can_send_messages === 1;
        $restrictionsStatus = $this->chat->newUserRestrictions->restrict_new_users === 1;

        $keyBoard = (new Buttons())->getNewUsersRestrictionsButtons(
            $canSendMessages,
            $canSendMedia,
            $restrictionsStatus
        );

        app("botService")->sendMessage(
            ResNewUsersCmd::SETTINGS->replyMessage(),
            $keyBoard
        );
    }

    public function sendRestrictionTimeButtons()
    {

        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_2H_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_24H_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_1W_CMD)
            ->addRow()
            ->addButton(CONSTANTS::RESTRICT_NEW_USERS_FOR_MONTH_CMD)
            ->get();

        app("botService")->sendMessage(
            CONSTANTS::REPLY_RESTRICT_SELECT_RESTRICTION_TIME_FOR_NEW_USERS,
            $keyBoard
        );
    }

    protected function setNewUsersRestrictionTime()
    {
        $this->botService->getChat()->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => $this->getRestrictionTime()
        ]);
        $this->sendNewRestrictionTimeReply();
        return $this;
    }

    protected function setNewUsersRestrictions(bool $canSendMessages = false, $canSendMedia = false)
    {
        $restrict = $canSendMessages || $canSendMedia ? 1 : 0;

        $this->botService->getChat()->newUsersRestrictions()->update([
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

    private function sendNewRestrictionTimeReply()
    {
        $message = match ($this->getRestrictionTime()) {
            CONSTANTS::RESTIME_2H => CONSTANTS::REPLY_RESTRICT_NEW_USERS_FOR_2H,
            CONSTANTS::RESTIME_DAY => CONSTANTS::REPLY_RESTRICT_NEW_USERS_FOR_24H,
            CONSTANTS::RESTIME_WEEK => CONSTANTS::REPLY_RESTRICT_NEW_USERS_FOR_1W,
            CONSTANTS::RESTIME_MONTH => CONSTANTS::REPLY_RESTRICT_NEW_USERS_FOR_MONTH,
            default => null
        };
        $this->botService->sendMessage($message);
    }
}
