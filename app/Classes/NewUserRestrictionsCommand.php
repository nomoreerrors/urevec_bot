<?php

namespace App\Classes;

use App\Enums\NewUserRestrictionsEnum;
use App\Enums\ResTime;
use App\Models\Chat;
use App\Models\NewUserRestriction;
use App\Services\BotErrorNotificationService;
use App\Services\TelegramBotService;
use App\Models\Admin;
use App\Classes\Menu;
use App\Traits\DynamicModel;
use App\Traits\RestrictionsTimeCases;
use App\Traits\RestrictionsCases;
use App\Traits\RestrictUsers;
use App\Traits\ToggleColumn;

class NewUserRestrictionsCommand extends BaseCommand
{
    use RestrictUsers;
    use ToggleColumn;
    use DynamicModel;

    public function __construct(protected TelegramBotService $botService)
    {
        $this->setModelFromClassName();
        parent::__construct($botService);
    }


    protected function handle(): void
    {
        parent::handle();
        $this->getRestrictionsCases();
        $this->getRestrictionTimeCases();
    }
}
