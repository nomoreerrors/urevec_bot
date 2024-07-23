<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use ErrorException;

class EnvironmentVariablesException extends BaseTelegramBotException
{

    public function __construct($message, $method)
    {
        parent::__construct($message, $method);
    }



    protected function sender(): static
    {
        BotErrorNotificationService::send($this->message);
        return $this;
    }
}
