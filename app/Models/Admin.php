<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        "admin_id",
        "is_bot",
        "first_name",
        "username",
        "language_code",
        "is_premium",
    ];


    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_admins', 'admin_id', 'chat_id')->withPivot("private_commands_access", "group_commands_access", "my_commands_set");
    }
}
