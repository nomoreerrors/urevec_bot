<?php

namespace App\Models\MessageModels\MediaModels;

use App\Traits\PhotoMediaTrait;
use App\Traits\VideoMediaTrait;
use App\Traits\VoiceMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class MultiMediaModel extends BaseMediaModel
{
    use PhotoMediaTrait,
        VideoMediaTrait;


    public function __construct()
    {
        parent::__construct();
    }
}
