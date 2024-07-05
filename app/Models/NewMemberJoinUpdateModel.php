<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewMemberJoinUpdateModel extends StatusUpdateModel
{
    use HasFactory;



    protected string $messageType = "chat_member";

    protected bool $isNewMemberJoinUpdate = true;

    protected bool $isNewChatMember = true;





    // public function __construct(array $data)
    // {
    //     parent::__construct($data);
    // }





}
