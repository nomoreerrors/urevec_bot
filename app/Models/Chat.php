<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ChatFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'chat_title',
    ];


    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'chat_admins', 'chat_id', 'admin_id')->withPivot('private_commands_access', 'group_commands_access', 'my_commands_set');
    }

    public function newUserRestrictions(): HasOne
    {
        return $this->hasOne(NewUserRestriction::class);
    }

    public function badWordsFilter(): HasOne
    {
        return $this->hasOne(BadWordsFilter::class);
    }

    public function unusualCharsFilter(): HasOne
    {
        return $this->hasOne(UnusualCharsFilter::class);
    }

    public function linksFilter(): HasOne
    {
        return $this->hasOne(LinksFilter::class);
    }

    public static function getDefinedRelationsNames(): array
    {
        $reflector = new \ReflectionClass(get_called_class());

        $result = collect($reflector->getMethods())
            ->filter(
                fn($method) => !empty ($method->getReturnType()) &&
                str_contains(
                    $method->getReturnType(),
                    'Illuminate\Database\Eloquent\Relations'
                )
            )
            ->pluck('name')
            ->all();

        return $result;
    }
}