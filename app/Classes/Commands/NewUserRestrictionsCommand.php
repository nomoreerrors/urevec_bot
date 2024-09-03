<?php

namespace App\Classes\Commands;

use App\Services\TelegramBotService;
use App\Traits\DynamicModel;
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
        $this->getRestrictionsCases();
        $this->getRestrictionTimeCases();
    }
}
