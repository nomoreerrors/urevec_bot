<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ChatFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'chat_title',
    ];


    protected static function newFactory(): ChatFactory
    {
        return ChatFactory::new();
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'chat_admins', 'chat_id', 'admin_id')->withPivot('private_commands_access', 'group_commands_access', 'my_commands_set');
    }
}
