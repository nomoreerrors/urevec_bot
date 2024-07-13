<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewMemberJoinUpdateModel extends StatusUpdateModel
{
    use HasFactory;

    /**
     * Class constructor.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
