<?php

namespace App\Models\Reactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseTelegramRequestModel;

class MessageReactionModel extends BaseTelegramRequestModel
{
    use HasFactory;

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
