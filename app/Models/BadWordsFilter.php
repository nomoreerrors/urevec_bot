<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadWordsFilter extends FilterModel
{
    use HasFactory;
    protected $table = 'bad_words_filter';

    protected $fillable = [
        'chat_id',
        'enabled',
        'delete_user',
        'restrict_user',
        'delete_message',
        'disable_send_messages',
        'can_send_messages',
        'can_send_media',
        'restriction_time',
        'bad_words',
        'bad_phrases',
        'critical_words',
        'critical_phrases',
    ];

    public function chats(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}