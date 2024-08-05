<?php

namespace App\Classes;

use App\Models\Chat;

class FilterSettingsCommand implements ReplyInterface
{
    public function __construct(private string $command, private Chat $chat)
    {
        //
    }
    public function send(): void
    {
        //
    }
}
