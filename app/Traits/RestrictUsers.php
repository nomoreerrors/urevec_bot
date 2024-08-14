<?php

namespace App\Traits;

use App\Classes\Buttons;
use App\Enums\ResTime;
use App\Classes\BackMenuButton;

trait RestrictUsers
{
    protected function setRestrictionTime(): void
    {
        $case = $this->enum::from($this->command);
        $this->model->update([
            "enabled" => 1,
            "restriction_time" => ResTime::getTime($case)
        ]);
        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }


    protected function sendRestrictionTimeButtons(): void
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->createButtons($this->getRestrictionsTimeTitles(), 1, true);
        $this->botService->sendMessage(
            $this->enum::SELECT_RESTRICTION_TIME->replyMessage(),
            $keyBoard
        );
    }

    /**
     * If disabled toggle only 'restrict_user' column
     * and if enabled toggle 'can_send_messages' and 'can_send_media to be enabled too (0)
     * @return void
     */
    protected function toggleAllRestrictions()
    {
        $this->model->update([
            'enabled' => $this->model->enabled ? 0 : 1
        ]);

        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    protected function toggleSendMedia()
    {
        $this->model->update([
            'can_send_media' => $this->model->can_send_media ? 0 : 1
        ]);

        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }


    protected function toggleSendMessages()
    {
        $this->chat->newUserRestrictions()->update([
            'can_send_messages' => $this->model->can_send_media ? 0 : 1
        ]);
        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }



    protected function getRestrictionsTimeTitles(): array
    {
        return [
            $this->enum::SET_TIME_MONTH->value,
            $this->enum::SET_TIME_WEEK->value,
            $this->enum::SET_TIME_DAY->value,
            $this->enum::SET_TIME_TWO_HOURS->value,
        ];
    }

    public function sendEditRestrictionsButtons()
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->createButtons($this->getEditRestrictionsTitles(), 1, true);
        app("botService")->sendMessage($this->enum::EDIT_RESTRICTIONS->replyMessage(), $keyBoard);
    }

    protected function getEditRestrictionsTitles(): array
    {
        return [
            $this->model->can_send_media ?
            $this->enum::SEND_MEDIA_DISABLE->value :
            $this->enum::SEND_MEDIA_ENABLE->value,

            $this->model->can_send_messages ?
            $this->enum::SEND_MESSAGES_DISABLE->value :
            $this->enum::SEND_MESSAGES_ENABLE->value,

            $this->model->enabled ?
            $this->enum::RESTRICTIONS_DISABLE_ALL->value :
            $this->enum::RESTRICTIONS_ENABLE_ALL->value,

            $this->enum::SELECT_RESTRICTION_TIME->value
        ];
    }


    public function sendRestrictionsTimeButtons()
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->createButtons($this->getRestrictionsTimeTitles(), 1, true);
        app("botService")->sendMessage($this->enum::SELECT_RESTRICTION_TIME->replyMessage(), $keyBoard);
    }
}
