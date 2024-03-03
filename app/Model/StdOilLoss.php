<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class StdOilLoss extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'std_oil_losses';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'group', 'product_name', 'parameter', 'value',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    public function mill()
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
