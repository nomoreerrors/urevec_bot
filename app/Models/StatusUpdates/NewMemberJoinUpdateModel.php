<?php

namespace App\Models\StatusUpdates;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewMemberJoinUpdateModel extends StatusUpdateModel
{
    use HasFactory;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
