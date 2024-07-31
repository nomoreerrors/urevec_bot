<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Relationships between admins and chats tables
 */
class ChatAdmins extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'chat_id',
        'private_commands_access',
        'group_commands_access',
        'my_commands_set'
    ];

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class)
            ->withPivot('private_commands_access', 'group_commands_access', 'my_commands_set');
    }

    /**
     * Get the admins that belong to the chat admins.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'chat_admins', 'admin_id', 'chat_id')
            ->withPivot('private_commands_access', 'group_commands_access', 'my_commands_set');
    }
}
