<?php

namespace App\Traits;

use App\Classes\Buttons;
use App\Services\BotErrorNotificationService;
use App\Enums\ResTime;
use App\Classes\Menu;

trait RestrictUsers
{
    // use Toggle;
    public function getRestrictionsCases()
    {
        switch ($this->command) {
            case $this->enum::EDIT_RESTRICTIONS->value:
                $this->sendEditRestrictionsButtons();
                break;
            case $this->enum::ENABLED_ENABLE->value:
            case $this->enum::ENABLED_DISABLE->value:
                $this->toggleColumn('enabled');
                break;
            case $this->enum::CAN_SEND_MEDIA_ENABLE->value:
            case $this->enum::CAN_SEND_MEDIA_DISABLE->value:
                $this->toggleColumn('can_send_media');
                break;
            case $this->enum::CAN_SEND_MESSAGES_ENABLE->value:
            case $this->enum::CAN_SEND_MESSAGES_DISABLE->value:
                $this->toggleColumn('can_send_messages');
                break;
        }
    }

    protected function getRestrictionTimeCases(): void
    {
        switch ($this->command) {
            case $this->enum::SELECT_RESTRICTION_TIME->value:
                $this->sendRestrictionsTimeButtons();
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


    public function sendEditRestrictionsButtons()
    {
        // BotErrorNotificationService::send("from Unucharscommand");
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getEditRestrictionsButtons($this->model, $this->enum);
        $this->botService->sendMessage($this->enum::EDIT_RESTRICTIONS->replyMessage(), $keyBoard);
    }



    public function sendRestrictionsTimeButtons()
    {
        $this->botService->menu()->save();
        $keyBoard = (new Buttons())->getRestrictionsTimeButtons($this->model, $this->enum);
        $this->botService->sendMessage($this->enum::SELECT_RESTRICTION_TIME->replyMessage(), $keyBoard);
    }


    // /**
    //  * NEW NEW NEW NEW NEW NEW
    //  * @return void
    //  */
    // protected function sendRestrictionsSettings(): void
    // {
    //     $this->botService->menu()->save();
    //     $keyBoard = (new Buttons())->create($this->enum::getRestrictionsCasesValues());
    //     $this->botService->sendMessage($this->enum::EDIT_RESTRICTIONS->replyMessage(), $keyBoard);
    // }

    // protected function sendRestrictionsTimeSettings(): void
    // {
    //     $this->botService->menu()->save();
    //     $keyBoard = (new Buttons())->create($this->enum::getRestrictionsTimeCasesValues());
    //     $this->botService->sendMessage($this->enum::SELECT_RESTRICTION_TIME->replyMessage(), $keyBoard);
    // }
}
