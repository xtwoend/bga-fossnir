<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class FossnirThreshold extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_threshold';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'group_id', 'threshold'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    /**
     * Relation to mill
     */
    public function mill() {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }

    /**
     * Relation to Fossnir Group
     */
    public function group() {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
