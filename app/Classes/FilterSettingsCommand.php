<?php

namespace App\Classes;

use App\Models\Chat;
use App\Enums\ResTime;
use App\Enums\BadWordsFilterCmd;
use App\Services\TelegramBotService;
use PHPUnit\Util\Filter;

class FilterSettingsCommand extends BaseCommand
{
    public function __construct(private string $command)
    {
        parent::__construct($this->command);
    }

    protected function handle(): static
    {
        switch ($this->command) {
            // TODO перенести в MainMenu и установить в privatecore if else
            case BadWordsFilterCmd::MAIN_SETTINGS->value:
                $this->send();
                break;
            case BadWordsFilterCmd::BAD_WORDS_SETTINGS->value:
                $this->sendBadWordsFIlterSettings();
                break;
            case BadWordsFilterCmd::BAD_WORDS_DISABLE->value:
            case BadWordsFilterCmd::BAD_WORDS_ENABLE->value:
                $this->toggleBadWordsFilter();
                break;
            case BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_DISABLE->value:
            case BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->value:
                $this->toggleBadWordsFilterDeleteMessages();
                break;
            case BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_DISABLE->value:
            case BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->value:
                $this->toggleBadWordsFilterRestrictUser();
                break;
            case BadWordsFilterCmd::SELECT_TIME->value:
                $this->sendBadWordsRestrictionTimeButtons();
                break;
            case BadWordsFilterCmd::SET_TIME_MONTH->value:
            case BadWordsFilterCmd::SET_TIME_WEEK->value:
            case BadWordsFilterCmd::SET_TIME_DAY->value:
            case BadWordsFilterCmd::SET_TIME_TWO_HOURS->value:
                $this->setBadWordsFilterRestrictTime();
                break;
            // default:
            //     break;
        }
        return $this;
    }


    public function send(): void
    {
        $keyBoard = (new Buttons())->getFiltersMainSettingsButtons();
        app("botService")->sendMessage(BadWordsFilterCmd::MAIN_SETTINGS->replyMessage(), $keyBoard);
    }


    private function sendBadWordsFIlterSettings(): void
    {
        $filterEnabled = $this->chat->badWordsFilter->filter_enabled === 1;
        $deleteMessagesEnabled = $this->chat->badWordsFilter->delete_message === 1;
        $restrictUsersEnabled = $this->chat->badWordsFilter->restrict_user === 1;


        $keyBoard = (new Buttons())->getBadWordsFilterSettingsButtons($filterEnabled, $deleteMessagesEnabled, $restrictUsersEnabled);
        app("botService")->sendMessage(BadWordsFilterCmd::BAD_WORDS_SETTINGS->replyMessage(), $keyBoard);
    }


    private function toggleBadWordsFilter()
    {
        $this->chat->badWordsFilter()->update([
            "filter_enabled" => $this->command === BadWordsFilterCmd::BAD_WORDS_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterCmd::from($this->command)->replyMessage());
    }

    private function toggleBadWordsFilterDeleteMessages()
    {
        $this->chat->badWordsFilter()->update([
            "delete_message" => $this->command === BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterCmd::from($this->command)->replyMessage());
    }

    private function toggleBadWordsFilterRestrictUser()
    {
        $this->chat->badWordsFilter()->update([
            "restrict_user" => $this->command === BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->value ? 1 : 0
        ]);

        $this->botService->sendMessage(BadWordsFilterCmd::from($this->command)->replyMessage());
    }


    public function sendBadWordsRestrictionTimeButtons()
    {
        $keyBoard = (new Buttons())->getBadWordsFilterRestrictionsTimeButtons();

        app("botService")->sendMessage(
            BadWordsFilterCmd::SELECT_TIME->replyMessage(),
            $keyBoard
        );
    }


    private function setBadWordsFilterRestrictTime()
    {
        $bwFilter = BadWordsFilterCmd::from($this->command);
        $this->chat->badWordsFilter()->update([
            "restrict_user" => 1,
            "restriction_time" => ResTime::getTime($bwFilter)
        ]);

        $this->botService->sendMessage(BadWordsFilterCmd::from($this->command)->replyMessage());
    }
}
