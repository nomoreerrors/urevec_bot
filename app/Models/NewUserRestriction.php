<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewUserRestriction extends Model
{
    use HasFactory;
    protected $table = 'new_users_restrictions';

    protected $fillable = [
        'chat_id',
        'enabled',
        'can_send_messages',
        'can_send_media',
        'restriction_time',
    ];

    public function chats(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}