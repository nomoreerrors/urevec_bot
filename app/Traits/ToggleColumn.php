<?php

namespace App\Traits;

use App\Classes\Menu;
use App\Services\BotErrorNotificationService;


/**
 * Toggle column in database and send message to user
 * with the same menu with refreshed button titles.
 *
 * If the menu refresh flag is set, this method will
 * simply return and do nothing, as the menu will be
 * refreshed in the menu class.
 */
trait ToggleColumn
{
    protected $model;

    protected function toggleColumn(string $column)
    {
        if ($this->botService->menu()->getIsMenuRefresh()) {
            $this->botService->menu()->setIsMenuRefresh(false);
            return;
        }
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
        $j = $this->enum::from($this->command)->replyMessage();
        $this->botService->sendMessage($this->enum::from($this->command)->replyMessage());
    }

    /**
     * Update menu titles after making changes in database
     * @return void
     */
    private function refreshMenu()
    {
        $this->botService->menu()->refresh();
    }
}




