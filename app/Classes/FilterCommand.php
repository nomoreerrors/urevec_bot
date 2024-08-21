<?php

namespace App\Classes;

use App\Interfaces\CommandEnumInterface;
use App\Enums\ResTime;
use App\Interfaces\FilterCmdEnumInterface;
use App\Models\FilterModel;
use App\Traits\RestrictionsTimeCases;
use App\Traits\RestrictionsCases;
use App\Traits\RestrictUsers;

class FilterCommand extends BaseCommand
{
    use RestrictUsers;
    /**
     * Summary of __construct
     * @param string $command
     * @param \App\Models\FilterModel $model
     * @param string $enum Enum::class
     */
    public function __construct(protected string $command, protected FilterModel $model, protected string $enum)
    {
        parent::__construct($command, $enum);
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

    protected function getSettingsTitles(): array
    {
        return [
            $this->model->enabled ?
            $this->enum::DISABLE->value :
            $this->enum::ENABLE->value,

            $this->model->delete_message ?
            $this->enum::DELETE_MESSAGES_DISABLE->value :
            $this->enum::DELETE_MESSAGES_ENABLE->value,

            $this->enum::EDIT_RESTRICTIONS->value
        ];
    }
}

