<?php

namespace App\Traits;

use App\Classes\Menu;
use App\Services\BotErrorNotificationService;

trait ToggleColumn
{
    protected $model;

    protected function toggleColumn(string $column)
    {
        $this->updateColumn($column);
        $this->sendToggleMessage();
        $this->refreshMenu();
    }

    private function updateColumn(string $column)
    {
        $this->model->update([
            $column => $this->model->{$column} ? 0 : 1
        ]);
    }

    private function sendToggleMessage()
    {
        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    /**
     * Update menu titles after making changes in database
     * @return void
     */
    private function refreshMenu()
    {
        (new Menu($this->botService))->refresh();
    }


}




