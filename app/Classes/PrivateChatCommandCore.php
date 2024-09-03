<?php

namespace App\Classes;

use App\Services\CONSTANTS;
use App\Classes\BaseCommand;
use App\Exceptions\UnknownChatException;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Log;

class PrivateChatCommandCore extends BaseBotCommandCore
{
    public function __construct(protected TelegramBotService $botService)
    {
        parent::__construct($botService);
        $this->command = $this->botService->getPrivateChatCommand();
    }


    public function handle(): void
    {
        if ($this->botService->menu()->backButtonPressed()) {
            // Some logic here
        } else {
            $this->botService->chatSelector()->select();

            if (
                $this->botService->chatSelector()->hasBeenUpdated() ||
                $this->botService->chatSelector()->buttonsHaveBeenSent()
            ) {
                return;
            }


            $commandClassName = (new CommandRouter($this->command))->getCommandClassName();
            $this->botService->createCommand($commandClassName);

            return;
        }
    }


    protected function checkUserAccess(): static
    {
        $admin = $this->botService->getAdmin();

        if (empty($admin)) {
            $error = CONSTANTS::USER_NOT_ALLOWED . " " . $admin->admin_id;
            log::info($error);

            $this->botService->sendMessage(CONSTANTS::ADD_BOT_TO_GROUP);
            throw new UnknownChatException($error, __METHOD__);
        }
        return $this;
    }

}
