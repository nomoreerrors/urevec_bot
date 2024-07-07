<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageReactionCountModel extends BaseTelegramRequestModel
{
    use HasFactory;

    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
