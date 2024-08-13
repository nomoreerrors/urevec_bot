<?php

namespace App\Classes;

use App\Enums\UnusualCharsFilterEnum;
use App\Interfaces\FilterCommand;
use App\Models\Chat;
use App\Enums\ResTime;
use App\Enums\BadWordsFilterEnum;
use App\Services\TelegramBotService;
use PHPUnit\Util\Filter;
use App\Classes\BackMenuButton;

class UnusualCharsFilterCommand extends FilterCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($this->command);
    }

    protected function handle(): static
    {
        switch ($this->command) {
            case UnusualCharsFilterEnum::SETTINGS->value:
                $this->send();
                break;
            case UnusualCharsFilterEnum::DISABLE->value:
            case UnusualCharsFilterEnum::ENABLE->value:
                $this->toggleFilter();
                break;
            case UnusualCharsFilterEnum::DELETE_MESSAGES_DISABLE->value:
            case UnusualCharsFilterEnum::DELETE_MESSAGES_ENABLE->value:
                $this->toggleDeleteMessages();
                break;
            case UnusualCharsFilterEnum::RESTRICT_USERS_DISABLE->value:
            case UnusualCharsFilterEnum::RESTRICT_USERS_ENABLE->value:
                $this->toggleRestrictUser();
                break;
            case UnusualCharsFilterEnum::SELECT_RESTRICTION_TIME->value:
                $this->sendRestrictionTimeButtons();
                break;
            case UnusualCharsFilterEnum::SET_TIME_MONTH->value:
            case UnusualCharsFilterEnum::SET_TIME_WEEK->value:
            case UnusualCharsFilterEnum::SET_TIME_DAY->value:
            case UnusualCharsFilterEnum::SET_TIME_TWO_HOURS->value:
                $this->setRestrictionTime();
                break;
            // default:
            //     break;
        }
        return $this;
    }

    public function send(): void
    {
        $filterEnabled = $this->chat->badWordsFilter->filter_enabled === 1;
        $deleteMessagesEnabled = $this->chat->badWordsFilter->delete_message === 1;
        $restrictUsersEnabled = $this->chat->badWordsFilter->restrict_user === 1;

        BackMenuButton::rememberBackMenu($this->command);
        $keyBoard = (new Buttons())->getBadWordsFilterSettingsButtons($filterEnabled, $deleteMessagesEnabled, $restrictUsersEnabled);
        app("botService")->sendMessage(BadWordsFilterEnum::SETTINGS->replyMessage(), $keyBoard);
    }


    protected function toggleFilter(): void
    {
        $this->chat->badWordsFilter()->update([
            "filter_enabled" => $this->command === BadWordsFilterEnum::ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterEnum::from($this->command)->replyMessage());
    }

    protected function toggleDeleteMessages(): void
    {
        $this->chat->badWordsFilter()->update([
            "delete_message" => $this->command === BadWordsFilterEnum::DELETE_MESSAGES_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterEnum::from($this->command)->replyMessage());
    }

    protected function toggleRestrictUser(): void
    {
        $this->chat->badWordsFilter()->update([
            "restrict_user" => $this->command === BadWordsFilterEnum::RESTRICT_USERS_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterEnum::from($this->command)->replyMessage());
    }


    protected function sendRestrictionTimeButtons(): void
    {
        $keyBoard = (new Buttons())->getBadWordsFilterRestrictionsTimeButtons();

        BackMenuButton::rememberBackMenu($this->command);
        app("botService")->sendMessage(
            BadWordsFilterEnum::SELECT_RESTRICTION_TIME->replyMessage(),
            $keyBoard
        );
    }


    protected function setRestrictionTime(): void
    {
        $bwFilter = BadWordsFilterEnum::from($this->command);
        $this->chat->badWordsFilter()->update([
            "restrict_user" => 1,
            "restriction_time" => ResTime::getTime($bwFilter)
        ]);
        $this->botService->sendMessage(BadWordsFilterEnum::from($this->command)->replyMessage());
    }
}
