<?php
namespace App\Exceptions;

use Exception;
use Throwable;

class InvalidCommandException extends Exception
{
    protected $message;
    protected $code = 400;
    protected $previous;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;

        parent::__construct($message, $code, $previous);
    }

    // public function __toString()
    // {
    //     return __CLASS__ . ": [{$this->code}]: {$this->message}";
    // }
}