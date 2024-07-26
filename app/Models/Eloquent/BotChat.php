<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'chat_title',
        'chat_admins',
        'message',
        'private_commands_access',
        'group_commands_access',
        'my_commands_set',
    ];

    /**
     * Automatically encode chat_admins array to json
     * @var array
     */
    protected $casts = [
        'chat_admins' => 'array',
        'private_commands_access' => 'array'
    ];

}
