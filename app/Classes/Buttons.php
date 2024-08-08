<?php

namespace App\Classes;

use App\Exceptions\BaseTelegramBotException;
use App\Enums\ResNewUsersCmd;

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
                ->addButton("/" . $title);
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
        $messages = $canSendMessages ?
            ResNewUsersCmd::DISABLE_SEND_MESSAGES->value :
            ResNewUsersCmd::ENABLE_SEND_MESSAGES->value;

        $media = $canSendMedia ?
            ResNewUsersCmd::DISABLE_SEND_MEDIA->value :
            ResNewUsersCmd::ENABLE_SEND_MEDIA->value;

        $restrictionsStatus = $settings ?
            ResNewUsersCmd::DISABLE_ALL->value :
            ResNewUsersCmd::ENABLE_ALL->value;

        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($messages)
            ->addRow()
            ->addButton($media)
            ->addRow()
            ->addButton($restrictionsStatus)
            ->get();

        return $keyBoard;
    }

    public function getRestrictionsTimeButtons(): array
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

        return $keyBoard;
    }
}