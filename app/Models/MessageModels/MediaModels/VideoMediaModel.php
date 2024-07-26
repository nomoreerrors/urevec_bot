<?php

namespace App\Models\MessageModels\MediaModels;

use App\Traits\VideoMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class VideoMediaModel extends BaseMediaModel
{
    use VideoMediaTrait;


    public function __construct(array $data)
    {
        parent::__construct($data);
    }



}
