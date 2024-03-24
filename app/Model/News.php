<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class News extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'news';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'content'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    /**
     * mill dir.
     */
    public function mill()
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
