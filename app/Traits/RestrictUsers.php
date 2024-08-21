<?php

namespace App\Traits;

use App\Classes\Buttons;
use App\Enums\ResTime;
use App\Classes\Menu;

trait RestrictUsers
{
    use Toggle;

    public function getRestrictionsCases()
    {
        switch ($this->command) {
            case $this->enum::EDIT_RESTRICTIONS->value:
                $this->sendEditRestrictionsButtons();
                break;
            case $this->enum::RESTRICTIONS_ENABLE_ALL->value:
            case $this->enum::RESTRICTIONS_DISABLE_ALL->value:
                $this->toggleColumn('enabled');
                break;
            case $this->enum::SEND_MEDIA_ENABLE->value:
            case $this->enum::SEND_MEDIA_DISABLE->value:
                $this->toggleColumn('can_send_media');
                break;
            case $this->enum::SEND_MESSAGES_ENABLE->value:
            case $this->enum::SEND_MESSAGES_DISABLE->value:
                $this->toggleColumn('can_send_messages');
                break;
        }
    }

    protected function getRestrictionTimeCases(): void
    {
        switch ($this->command) {
            case $this->enum::SELECT_RESTRICTION_TIME->value:
                $this->sendRestrictionTimeButtons();
                break;
            case $this->enum::SET_TIME_MONTH->value:
            case $this->enum::SET_TIME_WEEK->value:
            case $this->enum::SET_TIME_DAY->value:
            case $this->enum::SET_TIME_TWO_HOURS->value:
                $this->setRestrictionTime();
                break;
        }
    }

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
        Menu::save($this->command);
        $keyBoard = (new Buttons())->create($this->getRestrictionsTimeTitles(), 1, true);
        $this->botService->sendMessage(
            $this->enum::SELECT_RESTRICTION_TIME->replyMessage(),
            $keyBoard
        );
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
        Menu::save($this->command);
        $keyBoard = (new Buttons())->create($this->getEditRestrictionsTitles(), 1, true);
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
        Menu::save($this->command);
        $keyBoard = (new Buttons())->create($this->getRestrictionsTimeTitles(), 1, true);
        app("botService")->sendMessage($this->enum::SELECT_RESTRICTION_TIME->replyMessage(), $keyBoard);
    }
}
