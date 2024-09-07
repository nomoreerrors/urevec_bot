<?php

namespace App\Classes\Commands;

use App\Enums\Traits\EnumValues;
use App\Services\TelegramBotService;
use App\Traits\DynamicModel;
use App\Traits\RestrictUsers;
use App\Traits\ToggleColumn;
use App\Services\BotErrorNotificationService;

abstract class FilterCommand extends BaseCommand
{
    use RestrictUsers;
    use ToggleColumn;
    use DynamicModel;

    /**
     * Summary of __construct
     * @param string $command
     * @param \App\Models\FilterModel $model
     * @param string $enum Enum::class
     */
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

