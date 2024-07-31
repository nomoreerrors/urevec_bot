<?php

namespace App\Models\StatusUpdates;

use App\Exceptions\BaseTelegramBotException;
use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitedUserUpdateModel extends StatusUpdateModel
{
    use HasFactory;


    protected array $invitedUsersIdArray = [];

    public function __construct()
    {
        parent::__construct();
        $this->setInvitedUsersIdArray();
    }

    private function setInvitedUsersIdArray()
    {
        foreach (self::$data[self::$messageType]["new_chat_member"] as $member) {
            if (is_array($member) && array_key_exists("id", $member)) {
                $this->invitedUsersIdArray[] = $member["id"];
            }
        }
        return $this;
    }

    public function getInvitedUsersIdArray(): array
    {
        if (empty($this->invitedUsersIdArray)) {
            throw new BaseTelegramBotException("EMPTY INVITED USERS ID ARRAY", __METHOD__);
        }
        return $this->invitedUsersIdArray;
    }
}

