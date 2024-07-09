<?php

namespace Tests;

use App\Services\FilterService;
use App\Services\ManageChatSettingsService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Services\TelegramBotService;
use App\Models\TelegramMessageModel;
use App\Services\CONSTANTS;


abstract class TestCase extends BaseTestCase
{
    protected array $testObjects;

    protected $service;

    protected array $adminsIdArray;

    protected $chatPermissions;

    protected $filter;




    protected function setUp(): void
    {
        parent::setUp();
        $this->testObjects = json_decode(file_get_contents(__DIR__ . "/TestObjects.json"), true);
        $this->adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
        $this->chatPermissions = new ManageChatSettingsService();
    }
}
