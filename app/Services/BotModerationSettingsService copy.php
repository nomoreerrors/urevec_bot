<?php

namespace App\Services;

use App\Exceptions\TelegramModelException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class BotModerationSettingsService
{
    private array $flags = []; //USE ENUMS MAYBE??

}
