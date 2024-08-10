<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UnusualCharsFilter extends Model
{
    use HasFactory;

    protected $table = 'unusual_chars_filter';

    protected $fillable = [
        'filter_enabled',
        'delete_user',
        'restrict_user',
        'delete_message',
        'disable_send_messages',
        'restriction_time'
    ];

    public function chats(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
