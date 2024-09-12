<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinksFilter extends FilterModel
{
    use HasFactory;


    protected $table = 'links_filter';

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
