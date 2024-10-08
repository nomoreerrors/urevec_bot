<?php

namespace App\Models\MessageModels\MediaModels;

use App\Traits\PhotoMediaTrait;
use App\Traits\VoiceMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class VoiceMediaModel extends BaseMediaModel
{
    use VoiceMediaTrait;

    public function __construct()
    {
        parent::__construct();
    }
}
