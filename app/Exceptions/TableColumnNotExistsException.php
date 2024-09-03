<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use Exception;

class TableColumnNotExistsException extends Exception
{
    public function __construct(private string $columnName, private string $tableName, private string $method)
    {
        $this->setMessage();
    }

    public function setMessage()
    {
        $this->message = "Column $this->columnName does not exist in table $this->tableName in method $this->method";
    }
}
