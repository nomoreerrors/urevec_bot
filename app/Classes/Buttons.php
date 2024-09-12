<?php

namespace App\Classes;

use App\Enums\CommandEnums\MainMenuEnum;
use App\Enums\CommandEnums\LinksFilterEnum;
use App\Classes\Commands\MainMenuCommand;
use App\Enums\CommandEnums\UnusualCharsFilterEnum;
use App\Models\BadWordsFilter;
use App\Models\LinksFilter;
use App\Services\BotErrorNotificationService;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewUserRestriction;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\CommandEnums\ModerationSettingsEnum;
use App\Exceptions\BaseTelegramBotException;
use App\Enums\CommandEnums\BadWordsFilterEnum;
use App\Models\FilterModel;
use App\Models\UnusualCharsFilter;
use App\Services\CONSTANTS;
use App\Enums\CommandEnums\NewUserRestrictionsEnum;
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
                ->addButton(MainMenuEnum::BACK->value);
        }
        return $replyMarkup->get();
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
    public function getFiltersSettingsButtons(): array
    {
        $buttons = (new ButtonsTitles())->getFiltersSettingsTitles();
        $keyBoard = $this->create($buttons, 1, true);
        return $keyBoard;
    }


    public function getSelectChatButtons(array $titles): array
    {
        $keyBoard = $this->create($titles, 1, true);

        return $keyBoard;
    }


    public function getBadWordsFilterButtons(BadWordsFilter $filter, string $enum): array
    {
        $titles = (new ButtonsTitles($filter, $enum))->getBadWordsFilterTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    public function getUnusualCharsFilterButtons(UnusualCharsFilter $filter, string $enum): array
    {
        $titles = (new ButtonsTitles($filter, $enum))->getUnusualCharsFilterTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    public function getLinksFilterButtons(LinksFilter $filter, string $enum): array
    {
        $titles = (new ButtonsTitles($filter, $enum))->getLinksFilterTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }


    public function getRestrictionsTimeButtons(Model $model, string $enum): array
    {
        $titles = (new ButtonsTitles($model, $enum))->getRestrictionsTimeTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    public function getEditRestrictionsButtons(Model $model, string $enum): array
    {
        $titles = (new ButtonsTitles($model, $enum))->getEditRestrictionsTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    public function getEditRestrictionsTimeButtons(Model $model, string $enum): array
    {
        $titles = (new ButtonsTitles($model, $enum))->getRestrictionsTimeTitles();
        $keyBoard = $this->create($titles, 1, true);
        return $keyBoard;
    }

    // /**
    //  * NewUserRestriction base settings menu buttons 
    //  */
    // public function getNewUserRestrictionsButtons(NewUserRestriction $newUsersRestriction, string $enum): array
    // {
    //     $titles = (new ButtonsTitles($newUsersRestriction, $enum))->getNewUserRestrictionsTitles();
    //     $keyBoard = $this->create($titles, 1, true);
    //     return $keyBoard;
    // }
}