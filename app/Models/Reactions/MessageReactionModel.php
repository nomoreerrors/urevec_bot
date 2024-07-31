<?php

namespace App\Models\Reactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TelegramRequestModelBuilder;

class MessageReactionModel extends TelegramRequestModelBuilder
{
    use HasFactory;

    public function __construct()
    {
        //
    }
}
