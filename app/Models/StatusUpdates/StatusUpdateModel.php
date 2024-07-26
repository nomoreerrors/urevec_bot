<?php

namespace App\Models\StatusUpdates;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseTelegramRequestModel;

class StatusUpdateModel extends BaseTelegramRequestModel
{
    use HasFactory;


    protected string $messageType = "chat_member";


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setFromId()
            ->setFromUserName();
    }
}
