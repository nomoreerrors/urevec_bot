<?php

namespace App\Models\StatusUpdates;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TelegramRequestModelBuilder;

class StatusUpdateModel extends TelegramRequestModelBuilder
{
    use HasFactory;

    public function __construct()
    {
        $this->setFromId()
            ->setFromUserName();
    }
}
