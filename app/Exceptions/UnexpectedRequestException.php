<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use ErrorException;

class UnexpectedRequestException extends \Exception
{
    protected $code;

    public function __construct($message, $code = 0)
    {
        $this->code = $code;
        parent::__construct($message, $code);
    }
}
