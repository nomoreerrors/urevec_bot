<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use ErrorException;
use Illuminate\Support\Facades\Log;

class TelegramModelException extends ErrorException
{


    protected string $data = "";

    public function __construct(
        string $message = "",
        protected string $method,
    ) {
        parent::__construct($message);
        $this->data = print_r(request()->all(), true);


        $this->setMessage()
            ->sender();
    }


    protected function sender(): static
    {
        BotErrorNotificationService::send($this->message . PHP_EOL . $this->data);
        return $this;
    }


    protected function setMessage()
    {
        $this->message = "EXCEPTION CLASS: " . get_called_class() . PHP_EOL . $this->getMessage() . PHP_EOL .
            "LINE: " . $this->getLine() . PHP_EOL . "FROM METHOD: " . $this->method . PHP_EOL;

        return $this;
    }


    public function getData(): string
    {
        return $this->data;
    }



}
