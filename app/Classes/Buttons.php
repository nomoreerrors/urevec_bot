<?php

namespace App\Classes;

use App\Enums\UnusualCharsFilterCmd;
use App\Exceptions\BaseTelegramBotException;
use App\Enums\BadWordsFilterCmd;
use App\Models\UnusualCharsFilter;
use App\Services\CONSTANTS;
use App\Enums\ResNewUsersCmd;
use PHPUnit\Util\Filter;

class Buttons
{
    public function getSelectChatButtons(array $titles): array
    {
        if (empty($titles)) {
            throw new BaseTelegramBotException("Groups titles not set", __METHOD__);
        }

        $replyMarkup = new ReplyKeyboardMarkup();
        $i = 0;
        foreach ($titles as $title) {
            $replyMarkup->addRow()
                ->addButton($title);
            if (($i + 1) % 2 == 0) {
                $replyMarkup->addRow();
            }
            $i++;
        }
        return $replyMarkup->get();
    }

    /**
     * Define buttons names according to whether the value of setting in database disabled or not
     * @param bool $sendMessages
     * @param bool $sendMedia
     * @param bool $settings
     * @return array
     */
    public function getNewUsersRestrictionsButtons(bool $canSendMessages, bool $canSendMedia, bool $settings): array
    {
        $toggleSendMessages = $canSendMessages ?
            ResNewUsersCmd::DISABLE_SEND_MESSAGES->value :
            ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;

        $toggleSendMedia = $canSendMedia ?
            ResNewUsersCmd::DISABLE_SEND_MEDIA->value :
            ResNewUsersCmd::ENABLE_SEND_MEDIA->value;

        $toggleRestrictNewUsers = $settings ?
            ResNewUsersCmd::DISABLE_ALL->value :
            ResNewUsersCmd::ENABLE_ALL->value;

        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($toggleSendMessages)
            ->addRow()
            ->addButton($toggleSendMedia)
            ->addRow()
            ->addButton($toggleRestrictNewUsers)
            ->get();

        return $keyBoard;
    }

    public function getNewUsersRestrictionsTimeButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(ResNewUsersCmd::SET_TIME_TWO_HOURS->value)
            ->addRow()
            ->addButton(ResNewUsersCmd::SET_TIME_DAY->value)
            ->addRow()
            ->addButton(ResNewUsersCmd::SET_TIME_WEEK->value)
            ->addRow()
            ->addButton(ResNewUsersCmd::SET_TIME_MONTH->value)
            ->get();
        // getBadWordsRestrictionTimeButtons
        return $keyBoard;
    }

    public function getBadWordsFilterRestrictionsTimeButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(BadWordsFilterCmd::SET_TIME_TWO_HOURS->value)
            ->addRow()
            ->addButton(BadWordsFilterCmd::SET_TIME_DAY->value)
            ->addRow()
            ->addButton(BadWordsFilterCmd::SET_TIME_WEEK->value)
            ->addRow()
            ->addButton(BadWordsFilterCmd::SET_TIME_MONTH->value)
            ->get();

        return $keyBoard;
    }

    public function getModerationSettingsButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(ResNewUsersCmd::SETTINGS->value)
            ->addRow()
            ->addButton(BadWordsFilterCmd::MAIN_SETTINGS->value) //TODO change to enum
            ->get();

        return $keyBoard;
    }

    public function getFiltersMainSettingsButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(BadWordsFilterCmd::BAD_WORDS_SETTINGS->value)
            ->addRow()
            ->addButton(UnusualCharsFilterCmd::SETTINGS->value)
            ->get();

        return $keyBoard;
    }

    public function getBadWordsFilterSettingsButtons(bool $filterEnabled, bool $deleteMessagesEnabled, bool $restrictUsersEnabled): array
    {
        $toggleFIlter = $filterEnabled ?
            BadWordsFilterCmd::BAD_WORDS_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_ENABLE->value;

        $toggleDeleteMessage = $deleteMessagesEnabled ?
            BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_DELETE_MESSAGES_ENABLE->value;

        $toggleRestrictUser = $restrictUsersEnabled ?
            BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_DISABLE->value :
            BadWordsFilterCmd::BAD_WORDS_RESTRICT_USER_ENABLE->value;

        $restrictTime = BadWordsFilterCmd::SELECT_TIME->value;


        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($toggleFIlter)
            ->addRow()
            ->addButton($toggleDeleteMessage)
            ->addRow()
            ->addButton($toggleRestrictUser)
            ->addRow()
            ->addButton($restrictTime)
            ->get();

        return $keyBoard;
    }
}