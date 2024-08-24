<?php

namespace App\Classes;

use App\Services\TelegramBotService;
use App\Traits\DynamicModel;
use App\Traits\RestrictUsers;
use App\Traits\ToggleColumn;

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
        parent::handle();
        $this->getRestrictionsCases();
        $this->getRestrictionTimeCases();

        switch ($this->command) {
            case $this->enum::ENABLE->value:
            case $this->enum::DISABLE->value:
                $this->toggleColumn('enabled');
                break;
            case $this->enum::DELETE_MESSAGES_ENABLE->value:
            case $this->enum::DELETE_MESSAGES_DISABLE->value:
                $this->toggleColumn('delete_message');
                break;
        }
    }
}

