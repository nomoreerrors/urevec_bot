<?php

namespace App\Services;

use App\Exceptions\UnexpectedRequestException;
use App\Exceptions\EnvironmentVariablesException;
use App\Exceptions\UnknownIpAddressException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Storage;


class TelegramMiddlewareService
{
    private bool $typeIsExpected = false;

    private string $objectType = "";

    private int $chatId = 0;

    public function __construct(private array $data)
    {
        $this->checkIfObjectTypeExpected();
    }

    public function checkIfObjectTypeExpected(): void
    {
        $expectedTypes = ["message", "edited_message", "chat_member", "message_reaction"];

        foreach ($expectedTypes as $key) {
            if (array_key_exists($key, $this->data)) {
                $this->typeIsExpected = true;
                $this->setObjectType($key);
                $this->setChatId($key);
            }
        }

        if (!$this->typeIsExpected) {

            throw new UnexpectedRequestException(CONSTANTS::UNKNOWN_OBJECT_TYPE, __METHOD__);
        } else
            return;
    }


    private function setObjectType(string $key): void
    {
        $this->objectType = $key;
    }


    private function setChatId(string $key): void
    {
        $this->chatId = $this->data[$key]["chat"]["id"];
    }


    public function getObjectType(): string
    {
        return $this->objectType;
    }


    public function getChatId(): int
    {
        return $this->chatId;
    }


    public function checkIfIpAllowed(string $ip)
    {
        $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
        if (!in_array($ip, $allowedIps)) {
            throw new UnknownIpAddressException(CONSTANTS::REQUEST_IP_NOT_ALLOWED, __METHOD__);
        } else
            return;
    }


    public function validateEnvironmentVariables(string $first, string $second): void
    {
        if (empty($first) || empty($second)) {
            throw new EnvironmentVariablesException(CONSTANTS::EMPTY_ENVIRONMENT_VARIABLES, __METHOD__);
        }
        return;
    }
}
