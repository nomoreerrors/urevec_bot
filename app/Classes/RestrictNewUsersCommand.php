<?php

namespace App\Classes;

use App\Enums\ResNewUsersCmd;
use App\Enums\ResTime;
use App\Models\Chat;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;

class RestrictNewUsersCommand extends BaseCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($command);
    }

    protected function handle(): static
    {
        switch ($this->command) {
            case ResNewUsersCmd::SETTINGS->value:
                $this->send();
                break;
            case ResNewUsersCmd::SELECT_TIME->value:
                $this->sendRestrictionTimeButtons();
                break;
            case ResNewUsersCmd::SET_TIME_MONTH->value:
            case ResNewUsersCmd::SET_TIME_WEEK->value:
            case ResNewUsersCmd::SET_TIME_DAY->value:
            case ResNewUsersCmd::SET_TIME_TWO_HOURS->value:
                $this->setNewUsersRestrictionTime();
                break;
            case ResNewUsersCmd::ENABLE_ALL->value:
            case ResNewUsersCmd::DISABLE_ALL->value:
                $this->toggleAllRestrictions();
                break;
            case ResNewUsersCmd::ENABLE_SEND_MEDIA->value:
            case ResNewUsersCmd::DISABLE_SEND_MEDIA->value:
                $this->toggleSendMedia();
                break;
            case ResNewUsersCmd::ENABLE_SEND_MESSAGES->value:
            case ResNewUsersCmd::DISABLE_SEND_MESSAGES->value:
                $this->toggleSendMessages();
                break;
        }
        return $this;
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

        $this->rememberBackMenu();
        app("botService")->sendMessage(
            ResNewUsersCmd::SETTINGS->replyMessage(),
            $keyBoard
        );
    }

    public function sendRestrictionTimeButtons()
    {
        $keyBoard = (new Buttons())->getNewUsersRestrictionsTimeButtons();

        app("botService")->sendMessage(
            ResNewUsersCmd::SELECT_TIME->replyMessage(),
            $keyBoard
        );
    }

    protected function setNewUsersRestrictionTime()
    {
        $setTimeCase = ResNewUsersCmd::from($this->command);

        $this->botService->getChat()->newUserRestrictions()->update([
            'restrict_new_users' => 1,
            'restriction_time' => ResTime::getTime($setTimeCase)
        ]);
        $this->botService->sendMessage(ResNewUsersCmd::from($this->command)->replyMessage());
        return $this;
    }


    protected function getRestrictionTime(): ResTime
    {
        // BotErrorNotificationService::send($this->command);
        return match ($this->command) {
            ResNewUsersCmd::SET_TIME_MONTH->value => ResTime::MONTH,
            ResNewUsersCmd::SET_TIME_WEEK->value => ResTime::WEEK,
            ResNewUsersCmd::SET_TIME_DAY->value => ResTime::DAY,
            ResNewUsersCmd::SET_TIME_TWO_HOURS->value => ResTime::TWO_HOURS,
        };
    }

    /**
     * If disabled toggle only 'restrict_new_users' column
     * and if enabled toggle 'can_send_messages' and 'can_send_media to be enabled too (0)
     * @return void
     */
    protected function toggleAllRestrictions()
    {
        $enabled = $this->command === ResNewUsersCmd::ENABLE_ALL->value;
        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => $enabled ? 1 : 0,
            'can_send_messages' => $enabled ? 0 : $this->chat->newUserRestrictions->can_send_messages,
            'can_send_media' => $enabled ? 0 : $this->chat->newUserRestrictions->can_send_media
        ]);
        $this->botService->sendMessage(ResNewUsersCmd::from($this->command)->replyMessage());
    }

    protected function toggleSendMedia()
    {
        $isEnableCommand = $this->command === ResNewUsersCmd::ENABLE_SEND_MEDIA->value;
        $oldRestrictStatus = $this->chat->newUserRestrictions->restrict_new_users;

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => $isEnableCommand ? $oldRestrictStatus : 1,
            'can_send_media' => $isEnableCommand ? 1 : 0
        ]);

        $this->botService->sendMessage(ResNewUsersCmd::from($this->command)->replyMessage());
    }

    protected function toggleSendMessages()
    {
        $isEnableCommand = $this->command === ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;
        $oldRestrictStatus = $this->chat->newUserRestrictions->restrict_new_users;

        $this->chat->newUserRestrictions()->update([
            'restrict_new_users' => $isEnableCommand ? $oldRestrictStatus : 1,
            'can_send_messages' => $isEnableCommand ? 1 : 0
        ]);
        $this->botService->sendMessage(ResNewUsersCmd::from($this->command)->replyMessage());
    }

}
