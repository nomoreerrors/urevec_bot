<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForwardMessageModel extends MessageModel
{
    use HasFactory;



    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
