<?php

namespace App\Models\MessageModels\MediaModels;

use App\Traits\PhotoMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class PhotoMediaModel extends BaseMediaModel
{
    use PhotoMediaTrait;


    public function __construct(array $data)
    {
        parent::__construct($data);
        // $this->setMessageType();
        // dd($this->messageType);
        // dd($this->data);
    }
}
