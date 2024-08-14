<?php

namespace App\Traits;

trait RestrictionsTimeCases
{
    protected function getRestrictionTimeCases(): void
    {
        switch ($this->command) {
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
}
