<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UnusualCharsFilter extends FilterModel
{
    use HasFactory;

    protected $table = 'unusual_chars_filter';

    protected $fillable = [
        'chat_id',
        'enabled',
        'delete_user',
        'restrict_user',
        'delete_message',
        'can_send_messages',
        'can_send_media',
        'disable_send_messages',
        'restriction_time'
    ];

    public function chats(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
