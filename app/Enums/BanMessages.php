<?php

namespace App\Enums;

enum BanMessages: string
{
    case NEW_MEMBER_RESTRICTED = "НОВЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ. ";
    case INVITED_USER_BLOCKED = "НОВЫЙ ПРИГЛАШЕННЫЙ ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН НА СУТКИ.";
    case MEMBER_BLOCKED = "ПОЛЬЗОВАТЕЛЬ ЗАБЛОКИРОВАН.";


    public function withId(int $id)
    {
        return $this->value . " " . "user_id: " . $id;
    }
}