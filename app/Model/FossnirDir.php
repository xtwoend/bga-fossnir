<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class FossnirDir extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_dir';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_name', 'dir_path', 'auto_read'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];


    public function products() {
        return $this->hasMany(FossnirProduct::class, 'mill_id');
    }
}
