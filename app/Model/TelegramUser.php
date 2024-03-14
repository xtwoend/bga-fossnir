<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class TelegramUser extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'telegram_users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'last_chat_at' => 'datetime:Y-m-d H:i:s',
    ];
}
