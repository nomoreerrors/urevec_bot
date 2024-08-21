<?php

namespace App\Classes;

use App\Enums\UnusualCharsFilterEnum;
use App\Models\NewUserRestriction;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\ModerationSettingsEnum;
use App\Exceptions\BaseTelegramBotException;
use App\Enums\BadWordsFilterEnum;
use App\Models\FilterModel;
use App\Models\UnusualCharsFilter;
use App\Services\CONSTANTS;
use App\Enums\ResNewUsersEnum;
use Nette\ArgumentOutOfRangeException;
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

    public function create(array $titles, int $eachRowHas = 2, bool $withBackButton = false): array
    {
        if (empty($titles)) {
            throw new ArgumentOutOfRangeException("Buttons titles not set");
        }

        $replyMarkup = new ReplyKeyboardMarkup();
        $i = 0;
        $buttonsPerRow = 0;
        foreach ($titles as $title) {
            if ($buttonsPerRow == 0) {
                $replyMarkup->addRow();
            }
            $replyMarkup->addButton($title);
            $buttonsPerRow++;

            if ($buttonsPerRow == $eachRowHas) {
                $buttonsPerRow = 0;
            }
            $i++;
        }
        if ($withBackButton) {
            $replyMarkup->addRow()
                ->addButton(ModerationSettingsEnum::BACK->value);
        }
        return $replyMarkup->get();
    }

    // /**
    //  * Define buttons names according to whether the value of setting in database disabled or not
    //  * @param bool $sendMessages
    //  * @param bool $sendMedia
    //  * @param bool $settings
    //  * @return array
    //  */
    // public function getRestrictUsersButtons(NewUserRestriction $model, string $enum): array
    // {
    //     $canSendMedia = $model->can_send_media ?
    //             $model::SEND_MEDIA_DISABLE->value :
    //             $model::SEND_MEDIA_ENABLE->value;

    //     $canSendMessages = $model->can_send_messages ?
    //             $model::SEND_MESSAGES_DISABLE->value :
    //             $model::SEND_MESSAGES_ENABLE->value;

    //     $restrictNewUsers = $model->restrict_user ?
    //             $model::RESTRICTIONS_DISABLE_ALL->value :
    //             $model::RESTRICTIONS_ENABLE_ALL->value;

    //     $keyBoard = (new ReplyKeyboardMarkup())
    //         ->addRow()
    //         ->addButton($canSendMedia)
    //         ->addRow()
    //         ->addButton($canSendMessages)
    //         ->addRow()
    //         ->addButton($restrictNewUsers)
    //         ->addRow()
    //         ->addButton($enum::SELECT_RESTRICTION_TIME->value)
    //         ->addRow()
    //         ->addButton(MainMenuCmd::BACK->value)
    //         ->get();

    //     return $keyBoard;
    // }

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
            ->addButton(ModerationSettingsEnum::BACK->value)
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
            ->addButton(ModerationSettingsEnum::FILTERS_SETTINGS->value) //TODO change to enum
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
            ->addButton(ModerationSettingsEnum::BACK->value)
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
        $toggleFIlter = $filter->enabled ?
                $enum::DISABLE->value :
                $enum::ENABLE->value;

        $toggleDeleteMessage = $filter->delete_message ?
                $enum::DELETE_MESSAGES_DISABLE->value :
                $enum::DELETE_MESSAGES_ENABLE->value;

        $toggleRestrictUser = $filter->restrict_user ?
                $enum::RESTRICT_USERS_DISABLE->value :
                $enum::RESTRICT_USERS_ENABLE->value;

        $selectTime = $enum::SELECT_RESTRICTION_TIME->value;


        $keyBoard = (new ReplyKeyboardMarkup())
            ->addRow()
            ->addButton($toggleFIlter)
            ->addRow()
            ->addButton($toggleDeleteMessage)
            ->addRow()
            ->addButton($toggleRestrictUser)
            ->addRow()
            ->addButton($selectTime)
            ->addRow()
            ->addButton(ModerationSettingsEnum::BACK->value)
            ->get();

        return $keyBoard;
    }
}