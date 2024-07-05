<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusUpdateModel extends BaseTelegramRequestModel
{
    use HasFactory;


    protected string $messageType = "chat_member";


    public function __construct(array $data)
    {
        $this->data = $data;
        $this->setFromId()
            ->setFromUserName();
    }
}
