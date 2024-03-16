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

class GroupProduct extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_groups_products';

    /**
     * The attributes that are mass assignable.
     */
    protected array $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];


    /**
     * Relation to mill.
     */
    public function mill()
    {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }

    /**
     * Relation to Fossnir Group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
