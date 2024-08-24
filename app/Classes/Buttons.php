<?php

namespace App\Classes;

use App\Enums\UnusualCharsFilterEnum;
use App\Models\BadWordsFilter;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewUserRestriction;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\ModerationSettingsEnum;
use App\Exceptions\BaseTelegramBotException;
use App\Enums\BadWordsFilterEnum;
use App\Models\FilterModel;
use App\Models\UnusualCharsFilter;
use App\Services\CONSTANTS;
use App\Enums\NewUserRestrictionsEnum;
use Nette\ArgumentOutOfRangeException;
use PHPUnit\Util\Filter;
use App\Classes\ButtonsTitles;

class Buttons
{
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
        $titles = (new ButtonsTitles(null, ModerationSettingsEnum::class))
            ->getModerationSettingsTitles();

        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }


    /**
     * General menu of all filters handled in ModerationSettingsCommand class
     * @return array
     */
    public function getFiltersMenuSettingsButtons(): array
    {
        $buttons = (new ButtonsTitles())->getFiltersMenuSettingsTitles();
        $keyBoard = $this->create($buttons, 1, true);
        return $keyBoard;
    }

    // public function getRestrictNewUsersButtons(): array
    // {
    //     $buttons = [
    //         $this->model->enabled ?
    //         $this->enum::RESTRICTIONS_DISABLE_ALL->value :
    //         $this->enum::RESTRICTIONS_ENABLE_ALL->value,

    //         $this->enum::EDIT_RESTRICTIONS->value
    //     ];
    // }

    // /**
    //  * Summary of getFilterSettingsButtons
    //  * @param FilterModel $filter
    //  * @param string $enum Enum::class implements EnumHasRestrictionTimeInterface
    //  * @return array
    //  */
    // public function getFilterSettingsButtons(FilterModel $filter, string $enum): array
    // {
    //     $toggleFIlter = $filter->enabled ?
    //             $enum::DISABLE->value :
    //             $enum::ENABLE->value;

    //     $toggleDeleteMessage = $filter->delete_message ?
    //             $enum::DELETE_MESSAGES_DISABLE->value :
    //             $enum::DELETE_MESSAGES_ENABLE->value;

    //     $toggleRestrictUser = $filter->restrict_user ?
    //             $enum::RESTRICT_USERS_DISABLE->value :
    //             $enum::RESTRICT_USERS_ENABLE->value;

    //     $selectTime = $enum::SELECT_RESTRICTION_TIME->value;


    //     $keyBoard = (new ReplyKeyboardMarkup())
    //         ->addRow()
    //         ->addButton($toggleFIlter)
    //         ->addRow()
    //         ->addButton($toggleDeleteMessage)
    //         ->addRow()
    //         ->addButton($toggleRestrictUser)
    //         ->addRow()
    //         ->addButton($selectTime)
    //         ->addRow()
    //         ->addButton(ModerationSettingsEnum::BACK->value)
    //         ->get();

    //     return $keyBoard;
    // }

    public function getSelectChatButtons(array $titles): array
    {
        $keyBoard = $this->create($titles, 1, true);

        return $keyBoard;
    }

    /**
     * NewUserRestriction base settings menu buttons 
     */
    public function getNewUserRestrictionsButtons(NewUserRestriction $newUsersRestriction): array
    {
        $titles = (new ButtonsTitles($newUsersRestriction, NewUserRestrictionsEnum::class))->getNewUserRestrictionsTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    public function getBadWordsFilterButtons(BadWordsFilter $filter): array
    {
        $titles = (new ButtonsTitles($filter, BadWordsFilterEnum::class))->getBadWordsFilterTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }
}