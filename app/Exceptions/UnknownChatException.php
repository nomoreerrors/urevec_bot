<?php

namespace App\Exceptions;

use App\Services\BotErrorNotificationService;
use App\Services\CONSTANTS;
use ErrorException;

class UnknownChatException extends TelegramModelException
{
}
