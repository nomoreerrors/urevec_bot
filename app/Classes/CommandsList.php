<?php

namespace App\Classes;

use App\Services\CONSTANTS;

/**
 * Contains text of commands and descriptions 
 */
class CommandsList extends \stdClass
{
    private array $moderationSettings = [];



    public function __construct()
    {
        $this->setModerationSettings();
    }



    public function setModerationSettings(): void
    {
        $this->moderationSettings = [
            "command" => "moderation_settings", // Must be without a slash "/" 
            "description" => "TEST 234!!! Configure bot moderation settiings"
        ];
    }

    public function moderationSettings(): array
    {
        return $this->moderationSettings;
    }
}