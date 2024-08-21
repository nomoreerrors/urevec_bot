<?php

namespace App\Traits;
use App\Classes\Menu;

trait Toggle
{
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
        Menu::refresh();
    }
}
