<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use ErrorException;

class UnexpectedRequestMessage extends TelegramModelError
{

    public function __construct(
        string $message = "",
        protected string $data,
        protected string $method,
        protected string $called_class
    ) {
        parent::__construct($message, $data, $method, $called_class);

        $this->message = PHP_EOL . $message;


        // $this->info = $this->getMessage() . PHP_EOL . $this->getFile() . PHP_EOL .
        //     "AT LINE: " . $this->getLine() . PHP_EOL . "FROM METHOD: " . $method . PHP_EOL .
        //     "CALLED CLASS: " . $called_class . PHP_EOL;

        // $this->sender();
    }
}
