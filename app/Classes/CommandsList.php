<?php

namespace App\Classes;

use App\Services\CONSTANTS;

/**
 * Contains text of commands and descriptions 
 */
class CommandsList extends \stdClass
{
    public $moderationSettings = [];

    public $testCommand = [];


    public function __construct()
    {
        $this->setModerationSettings();
        $this->setTestCommand();
    }

    public function setTestCommand(): void
    {
        $this->testCommand = (object) [
            "command" => "test_command",
            "description" => "TEST 234!!!"
        ];
    }

    public function setModerationSettings(): void
    {
        $this->moderationSettings = (object) [
            "command" => "moderation_settings", // Must be without a slash "/" 
            "description" => "TEST 234!!! Configure bot moderation settiings"
        ];
    }
}