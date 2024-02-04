<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 */
class FossnirProduct extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'fossnir_products';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'mill_id', 'product_name', 'parameter', 'std_value'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    /**
     * 
     */
    public function mill() {
        return $this->belongsTo(FossnirDir::class, 'mill_id');
    }
}
