<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Services\TelegramBotService;

abstract class TestCase extends BaseTestCase
{
    protected array $testObjects;

    protected $service;

    protected array $adminsIdArray;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObjects = json_decode(file_get_contents(__DIR__ . "/TestObjects.json"), true);
        $this->service = new TelegramBotService();
        $this->adminsIdArray = explode(",", env("TELEGRAM_CHAT_ADMINS_ID"));
    }
}
