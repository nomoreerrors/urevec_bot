<?php

namespace App\Classes;

use App\Enums\NewUserRestrictionsEnum;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UnusualCharsFilterEnum;
use App\Enums\BadWordsFilterEnum;

class ButtonsTitles
{
    /**
     * Summary of __construct
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param mixed $enum Enum::class
     */
    public function __construct(protected ?Model $model = null, protected $enum = null)
    {
        //
    }
    public function getRestrictionsTitles()
    {
        return [
            $this->model->enabled ?
            $this->enum::RESTRICTIONS_DISABLE->value :
            $this->enum::RESTRICTIONS_ENABLE->value,

            $this->enum::EDIT_RESTRICTIONS->value
        ];
    }

    public function getNewUserRestrictionsTitles()
    {
        return array_merge(
            $this->getRestrictionsTitles(),
        );
    }

    public function getFilterSettingsTitles()
    {
        return array_merge(
            $this->getRestrictionsTitles(),
            [
                $this->model->delete_message ?
                $this->enum::DELETE_MESSAGES_DISABLE->value :
                $this->enum::DELETE_MESSAGES_ENABLE->value,
            ]
        );
    }

    public function getBadWordsFilterTitles()
    {
        return array_merge(
            $this->getFilterSettingsTitles(),
            [
                $this->enum::ADD_WORDS->value,
                $this->enum::DELETE_WORDS->value,
                $this->enum::GET_WORDS->value,
            ]
        );
    }

    public function getModerationSettingsTitles()
    {
        return [
            $this->enum::FILTERS_SETTINGS->value,
            NewUserRestrictionsEnum::SETTINGS->value,
            $this->enum::SELECT_CHAT->value
        ];
    }

    public function getFiltersMenuSettingsTitles()
    {
        $buttons = [
            NewUserRestrictionsEnum::SETTINGS->value,
            BadWordsFilterEnum::SETTINGS->value,
            UnusualCharsFilterEnum::SETTINGS->value
        ];
        return $buttons;
    }
}
