<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use ErrorException;
use Illuminate\Support\Facades\Log;

class TelegramModelError extends ErrorException
{

    protected string $info = "";


    public function __construct(
        string $message = "",
        protected string $data,
        protected string $method,
        protected string $called_class
    ) {
        parent::__construct($message);
        $this->message = PHP_EOL . $message;
        $this->data = print_r(json_decode($data, true), true);

        $this->info = "EXCEPTION CLASS: " . get_called_class() . PHP_EOL . $this->getMessage() . PHP_EOL . $this->getFile() . PHP_EOL .
            "AT LINE: " . $this->getLine() . PHP_EOL . "FROM METHOD: " . $method . PHP_EOL;

        $this->sender();
    }


    private function sender(): void
    {
        // dd($this->info);
        BotErrorNotificationService::send($this->info . PHP_EOL . $this->data);
    }




    public function getData(): string
    {
        return $this->data;
    }


    public function getInfo(): string
    {
        return $this->info;
    }
}
