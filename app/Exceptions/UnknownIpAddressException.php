<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use Illuminate\Http\Request;
use ErrorException;

class UnknownIpAddressException extends BaseTelegramBotException
{

    private string $ip;

    public function __construct($message, $method)
    {
        $this->ip = request()->ip();
        parent::__construct($message, $method);
    }


    protected function setMessage()
    {
        $this->message = "EXCEPTION CLASS: " . get_called_class() . PHP_EOL . $this->getMessage() . PHP_EOL .
            "IP: " . $this->ip . PHP_EOL .
            "LINE: " . $this->getLine() . PHP_EOL . "FROM METHOD: " . $this->method . PHP_EOL;

        return $this;
    }


    protected function sender(): static
    {
        BotErrorNotificationService::send($this->message);
        return $this;
    }
}
