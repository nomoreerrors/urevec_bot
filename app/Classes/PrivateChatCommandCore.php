<?php

namespace App\Classes;

use App\Services\BotErrorNotificationService;
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
        $this->updateCommandIfChanged();

        if ($this->botService->menu()->backButtonPressed()) {
            //  
        } else {
            $this->botService->chatSelector()->select();

            if (
                $this->botService->chatSelector()->buttonsHaveBeenSent() ||
                $this->botService->chatSelector()->hasBeenUpdated()
            ) {
                // exit; //!!!!!!!!
                return;
            }
        }


        $commandClassName = $this->getCommandClassName();

        if (!$this->isValidCommandClassName($commandClassName)) {
            return;
        }

        //TODO change the name to executeCommand 
        // to make it more readable
        $this->botService->createCommand($commandClassName);
    }



    /**
     * Find the command  in the enum classes at  /Enums/CommandEnums
     * @return string|null
     */
    protected function getCommandClassName(): ?string
    {
        return (new CommandRouter($this->command))->getCommandClassName();
    }

    /**
     * Send a message to the user if the command is not found
     * @param mixed $commandClassName
     * @return bool
     */
    protected function isValidCommandClassName(?string $commandClassName): bool
    {
        if (!$commandClassName) {
            $this->botService->sendMessage(CONSTANTS::COMMAND_NOT_FOUND);
            BotErrorNotificationService::send($this->command . " " . CONSTANTS::COMMAND_NOT_FOUND);
            return false;
        }
        return true;
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

    /**
     * updates the command for the case it has been changed in chatSelector or Menu class
     * @return void
     */
    protected function updateCommandIfChanged(): void
    {
        $this->command = $this->botService->getPrivateChatCommand();
    }


}
