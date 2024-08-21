<?php

namespace App\Classes;

use App\Enums\ResTime;
use App\Models\Chat;
use App\Models\NewUserRestriction;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;
use App\Models\Admin;
use App\Classes\Menu;
use App\Traits\RestrictionsTimeCases;
use App\Traits\RestrictionsCases;
use App\Traits\RestrictUsers;
use App\Traits\Toggle;

class RestrictNewUsersCommand extends BaseCommand
{
    /**
     * Summary of __construct
     * @param string $command
     * @param \App\Models\NewUserRestriction $model 
     * @param string $enum Enum::class 
     */
    public function __construct(protected string $command, protected NewUserRestriction $model, protected string $enum)
    {
        parent::__construct($command, $enum);
    }


    protected function handle(): void
    {
        parent::handle();
        $this->getRestrictionsCases();
        $this->getRestrictionTimeCases();
    }

    protected function getSettingsTitles(): array
    {
        return [
            $this->model->enabled ?
            $this->enum::RESTRICTIONS_DISABLE_ALL->value :
            $this->enum::RESTRICTIONS_ENABLE_ALL->value,

            $this->enum::EDIT_RESTRICTIONS->value
        ];
    }


}
