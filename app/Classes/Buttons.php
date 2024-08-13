<?php

namespace App\Classes;

use App\Enums\UnusualCharsFilterEnum;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\MainMenuCmd;
use App\Exceptions\BaseTelegramBotException;
use App\Enums\BadWordsFilterEnum;
use App\Models\FilterModel;
use App\Models\UnusualCharsFilter;
use App\Services\CONSTANTS;
use App\Enums\ResNewUsersEnum;
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
            ResNewUsersEnum::DISABLE_SEND_MESSAGES->value :
            ResNewUsersEnum::ENABLE_SEND_MESSAGES->value;

        $toggleSendMedia = $canSendMedia ?
            ResNewUsersEnum::DISABLE_SEND_MEDIA->value :
            ResNewUsersEnum::ENABLE_SEND_MEDIA->value;

        $toggleRestrictNewUsers = $settings ?
            ResNewUsersEnum::DISABLE_ALL->value :
            ResNewUsersEnum::ENABLE_ALL->value;

        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($toggleSendMessages)
            ->addRow()
            ->addButton($toggleSendMedia)
            ->addRow()
            ->addButton($toggleRestrictNewUsers)
            ->addRow()
            ->addButton(ResNewUsersEnum::SELECT_RESTRICTION_TIME->value)
            ->addRow()
            ->addButton(MainMenuCmd::BACK->value)
            ->get();

        return $keyBoard;
    }

    /**
     * Summary of getRestrictionsTimeButtons
     * @param string $enum Enum::class implements EnumHasRestrictionTimeInterface
     * @return array
     */
    public function getRestrictionsTimeButtons(string $enum): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($enum::SET_TIME_TWO_HOURS->value)
            ->addRow()
            ->addButton($enum::SET_TIME_DAY->value)
            ->addRow()
            ->addButton($enum::SET_TIME_WEEK->value)
            ->addRow()
            ->addButton($enum::SET_TIME_MONTH->value)
            ->addRow()
            ->addButton(MainMenuCmd::BACK->value)
            ->get();
        // getBadWordsRestrictionTimeButtons
        return $keyBoard;
    }


    public function getModerationSettingsButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(ResNewUsersEnum::SETTINGS->value)
            ->addRow()
            ->addButton(MainMenuCmd::FILTERS_SETTINGS->value) //TODO change to enum
            ->get();

        return $keyBoard;
    }

    public function getFiltersMainSettingsButtons(): array
    {
        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton(BadWordsFilterEnum::SETTINGS->value)
            ->addRow()
            ->addButton(UnusualCharsFilterEnum::SETTINGS->value)
            ->addRow()
            ->addButton(MainMenuCmd::BACK->value)
            ->get();

        return $keyBoard;
    }

    /**
     * Summary of getFilterSettingsButtons
     * @param FilterModel $filter
     * @param string $enum Enum::class implements EnumHasRestrictionTimeInterface
     * @return array
     */
    public function getFilterSettingsButtons(FilterModel $filter, string $enum): array
    {
        $lol = $filter->filter_enabled;
        $toggleFIlter = $filter->filter_enabled === 1;
        $toggleDeleteMessage = $filter->delete_message === 1;
        $toggleRestrictUser = $filter->restrict_user === 1;

        $toggleFIlter = $toggleFIlter ?
                $enum::DISABLE->value :
                $enum::ENABLE->value;

        $toggleDeleteMessage = $toggleDeleteMessage ?
                $enum::DELETE_MESSAGES_DISABLE->value :
                $enum::DELETE_MESSAGES_ENABLE->value;

        $toggleRestrictUser = $toggleRestrictUser ?
                $enum::RESTRICT_USERS_DISABLE->value :
                $enum::RESTRICT_USERS_ENABLE->value;

        $restrictTime = $enum::SELECT_RESTRICTION_TIME->value;


        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($toggleFIlter)
            ->addRow()
            ->addButton($toggleDeleteMessage)
            ->addRow()
            ->addButton($toggleRestrictUser)
            ->addRow()
            ->addButton($restrictTime)
            ->addRow()
            ->addButton(MainMenuCmd::BACK->value)
            ->get();

        return $keyBoard;
    }
}