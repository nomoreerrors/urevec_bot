<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use ErrorException;
use Illuminate\Support\Facades\Log;

class SetCommandsFailedException extends BaseTelegramBotException
{
    protected string $data = "";

    public function __construct(
        protected array $expectedCommands,
        protected array $actualCommands
    ) {
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
        $this->message = "EXCEPTION: " . get_called_class() . PHP_EOL .
            "EXPECTED COMMANDS: " .
            json_encode($this->expectedCommands) . PHP_EOL .
            "ACTUAL COMMANDS: " . PHP_EOL .
            json_encode($this->actualCommands) . PHP_EOL .
            "LINE: " . $this->getLine() . PHP_EOL;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
