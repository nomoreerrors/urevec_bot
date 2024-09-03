<?php

namespace App\Exceptions;

use Exception;

class EmptyTitlesArrayException extends Exception
{
    public function __construct(string $method)
    {
        parent::__construct('EMPTY TITLES ARRAY' . PHP_EOL . $method);
    }

}
