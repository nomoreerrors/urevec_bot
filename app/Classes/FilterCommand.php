<?php

namespace App\Classes;

use App\Interfaces\CommandEnumInterface;
use App\Enums\ResTime;
use App\Interfaces\FilterCmdEnumInterface;
use App\Models\FilterModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FilterCommand extends BaseCommand
{
    /**
     * Summary of __construct
     * @param string $command
     * @param \App\Models\FilterModel $filter
     * @param string $enum Enum::class
     */
    public function __construct(protected string $command, protected FilterModel $filter, protected string $enum)
    {
        parent::__construct($command, $enum);
    }

    protected function handle()
    {
        switch ($this->command) {
            case $this->enum::SETTINGS->value:
                $this->send();
                break;
            case $this->enum::DISABLE->value:
            case $this->enum::ENABLE->value:
                $this->toggleFilter();
                break;
            case $this->enum::DELETE_MESSAGES_DISABLE->value:
            case $this->enum::DELETE_MESSAGES_ENABLE->value:
                $this->toggleDeleteMessages();
                break;
            case $this->enum::RESTRICT_USERS_DISABLE->value:
            case $this->enum::RESTRICT_USERS_ENABLE->value:
                $this->toggleRestrictUser();
                break;
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

    public function send(): void
    {
        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = $this->getSettingsButtons();
        app("botService")->sendMessage($this->enum::SETTINGS->replyMessage(), $keyBoard);
    }

    protected function getMenuButtons(): array
    {
        return [];
    }

    protected function toggleFilter(): void
    {
        $this->filter->update([
            "filter_enabled" => $this->command === $this->enum::ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    protected function toggleDeleteMessages(): void
    {
        $this->filter->update([
            "delete_message" => $this->command === $this->enum::DELETE_MESSAGES_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    protected function toggleRestrictUser(): void
    {
        $this->filter->update([
            "restrict_user" => $this->command === $this->enum::RESTRICT_USERS_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    protected function setRestrictionTime(): void
    {
        $case = $this->enum::from($this->command);
        $this->filter->update([
            "restrict_user" => 1,
            "restriction_time" => ResTime::getTime($case)
        ]);
        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    protected function sendRestrictionTimeButtons(): void
    {
        $keyBoard = (new Buttons())->getRestrictionsTimeButtons($this->enum);
        BackMenuButton::rememberBackMenu($this->command);
        $this->botService->sendMessage(
            $this->enum::SELECT_RESTRICTION_TIME->replyMessage(),
            $keyBoard
        );
    }


    protected function getSettingsButtons(): array
    {
        return (new Buttons())->getFilterSettingsButtons($this->filter, $this->enum);
    }
}

