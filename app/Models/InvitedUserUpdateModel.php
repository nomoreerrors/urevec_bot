<?php

namespace App\Models;

use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitedUserUpdateModel extends StatusUpdateModel
{
    use HasFactory;


    protected string $messageType = "chat_member";

    protected array $invitedUsersIdArray = [];


    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->setInvitedUsersIdArray();
    }



    protected function setInvitedUsersIdArray()
    {
        foreach ($this->data[$this->messageType]["new_chat_member"] as $member) {
            if (is_array($member) && array_key_exists("id", $member)) {
                $this->invitedUsersIdArray[] = $member["id"];
            }
        }
        return $this;
    }


    public function getInvitedUsersIdArray(): array
    {
        return $this->invitedUsersIdArray;
    }
}
