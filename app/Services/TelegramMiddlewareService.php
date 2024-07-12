<?php

namespace App\Services;

use App\Exceptions\UnexpectedRequestException;
use App\Exceptions\UnknownChatException;
use App\Exceptions\UnknownIpAddressException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\CONSTANTS;
use Illuminate\Support\Facades\Storage;


class TelegramMiddlewareService 
{

    private bool $typeIsExpected = false;

    public function __construct(private array $data) {}


    public function checkIfObjectTypeExpected()
    {
            $expectedTypes = ["message", "edited_message", "chat_member", "message_reaction"];

            foreach ($expectedTypes as $key) {
                if (array_key_exists($key, $this->data)) {
                $this->typeIsExpected = true;
                };
            }
            
            if(!$this->typeIsExpected)  {
                
                throw new UnexpectedRequestException(CONSTANTS::UNKNOWN_OBJECT_TYPE, __METHOD__);
            } else
            return;
    }


    public function checkIfChatIdAllowed(int $chatId)
    {
        $allowedChats = explode(",", env("ALLOWED_CHATS_ID"));

         if (!in_array($chatId, $allowedChats)) {
            // dd($chatId);
                    throw new UnknownChatException(CONSTANTS::REQUEST_CHAT_ID_NOT_ALLOWED, __METHOD__);
        } else {
            return;
        }

    }


    public function checIfIpAllowed(string $ip)
    {
          $allowedIps = explode(",", env("ALLOWED_IP_ADRESSES"));
          if (!in_array($ip, $allowedIps)) {
                    throw new UnknownIpAddressException(CONSTANTS::REQUEST_IP_NOT_ALLOWED, __METHOD__);
        } else
            return;
    }
}
